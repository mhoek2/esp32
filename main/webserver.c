#include "common.h"
#include "config.h"
#include "wifi.h"

#include "esp_http_server.h"
#include <esp_log.h>

// https://github.com/espressif/arduino-esp32/blob/master/tools/partitions/min_spiffs.csv
// https://github.com/espressif/esp-idf/blob/master/examples/protocols/http_server/simple/main/main.c

#define HTTP_QUERY_KEY_MAX_LEN  64

static const char *TAG = "webserver";

#define MAX_SSID_LENGTH     32
#define MAX_PASS_LENGTH     64

#define PAGE_STA_SETUP      0
#define PAGE_SERVER_SETUP   1
#define PAGE_OVERVIEW       2

/* Type of Escape algorithms to be used */
#define NGX_ESCAPE_URI            (0)
#define NGX_ESCAPE_ARGS           (1)
#define NGX_ESCAPE_URI_COMPONENT  (2)
#define NGX_ESCAPE_HTML           (3)
#define NGX_ESCAPE_REFRESH        (4)
#define NGX_ESCAPE_MEMCACHED      (5)
#define NGX_ESCAPE_MAIL_AUTH      (6)

/* Type of Unescape algorithms to be used */
#define NGX_UNESCAPE_URI          (1)
#define NGX_UNESCAPE_REDIRECT     (2)

void ngx_unescape_uri(u_char **dst, u_char **src, size_t size, unsigned int type)
{
    u_char  *d, *s, ch, c, decoded;
    enum {
        sw_usual = 0,
        sw_quoted,
        sw_quoted_second
    } state;

    d = *dst;
    s = *src;

    state = 0;
    decoded = 0;

    while (size--) {

        ch = *s++;

        switch (state) {
        case sw_usual:
            if (ch == '?'
                    && (type & (NGX_UNESCAPE_URI | NGX_UNESCAPE_REDIRECT))) {
                *d++ = ch;
                goto done;
            }

            if (ch == '%') {
                state = sw_quoted;
                break;
            }

            *d++ = ch;
            break;

        case sw_quoted:

            if (ch >= '0' && ch <= '9') {
                decoded = (u_char) (ch - '0');
                state = sw_quoted_second;
                break;
            }

            c = (u_char) (ch | 0x20);
            if (c >= 'a' && c <= 'f') {
                decoded = (u_char) (c - 'a' + 10);
                state = sw_quoted_second;
                break;
            }

            /* the invalid quoted character */

            state = sw_usual;

            *d++ = ch;

            break;

        case sw_quoted_second:

            state = sw_usual;

            if (ch >= '0' && ch <= '9') {
                ch = (u_char) ((decoded << 4) + (ch - '0'));

                if (type & NGX_UNESCAPE_REDIRECT) {
                    if (ch > '%' && ch < 0x7f) {
                        *d++ = ch;
                        break;
                    }

                    *d++ = '%'; *d++ = *(s - 2); *d++ = *(s - 1);

                    break;
                }

                *d++ = ch;

                break;
            }

            c = (u_char) (ch | 0x20);
            if (c >= 'a' && c <= 'f') {
                ch = (u_char) ((decoded << 4) + (c - 'a') + 10);

                if (type & NGX_UNESCAPE_URI) {
                    if (ch == '?') {
                        *d++ = ch;
                        goto done;
                    }

                    *d++ = ch;
                    break;
                }

                if (type & NGX_UNESCAPE_REDIRECT) {
                    if (ch == '?') {
                        *d++ = ch;
                        goto done;
                    }

                    if (ch > '%' && ch < 0x7f) {
                        *d++ = ch;
                        break;
                    }

                    *d++ = '%'; *d++ = *(s - 2); *d++ = *(s - 1);
                    break;
                }

                *d++ = ch;

                break;
            }

            /* the invalid quoted character */

            break;
        }
    }

done:

    *dst = d;
    *src = s;
}

void example_uri_decode(char *dest, const char *src, size_t len)
{
    if (!src || !dest) {
        return;
    }

    unsigned char *src_ptr = (unsigned char *)src;
    unsigned char *dst_ptr = (unsigned char *)dest;
    ngx_unescape_uri(&dst_ptr, &src_ptr, len, NGX_UNESCAPE_URI);
}

static const char *webpages[3] = {
    "/spiffs/web/sta_setup.html",
    "/spiffs/web/server_setup.html",
    "/spiffs/web/overview.html",
};

static const char *get_root_html_path( config_t *config )
{
    // get the html path based on current configuration
    // initial-state:   sta setup
    // sta-set:         server setup
    // server-set:      overview page

    uint16_t page_index = PAGE_STA_SETUP;

    if ( config->sta_initialized )
        page_index = PAGE_SERVER_SETUP;

    if ( config->server_initialized )
        page_index = PAGE_OVERVIEW;
    
    return webpages[page_index];
}

static esp_err_t root_get_handler( httpd_req_t *req )
{
    FILE *html_file;
    
    char buffer[1024];
    size_t read_bytes;
    config_t *config = get_config();

    const char *html_path = get_root_html_path( config );

    html_file = fopen( html_path, "r" );

    if ( !html_file ) 
    {
        httpd_resp_send_500(req);
        return ESP_FAIL;
    }

    while ( ( read_bytes = fread( buffer, 1, sizeof(buffer), html_file ) ) > 0 ) 
    {
        httpd_resp_send_chunk( req, buffer, read_bytes );
    }

    fclose(html_file);
    httpd_resp_send_chunk( req, NULL, 0 );

    ESP_LOGI( TAG, "Request html %s", html_path );

    return ESP_OK;
}

// assets
static esp_err_t style_css_get_handler(httpd_req_t *req)
{
    FILE *asset_file = fopen("/spiffs/web/style.css", "r");

    if ( !asset_file ) 
    {
        httpd_resp_send_404(req);
        return ESP_FAIL;
    }

    httpd_resp_set_type(req, "text/css");

    char buffer[1024];
    size_t read_bytes;

    while ( ( read_bytes = fread( buffer, 1, sizeof(buffer), asset_file ) ) > 0 ) {
        httpd_resp_send_chunk(req, buffer, read_bytes);
    }

    fclose(asset_file);
    httpd_resp_send_chunk( req, NULL, 0 );

    return ESP_OK;
}

// xhr requests
static esp_err_t xhr_invalid_mode( httpd_req_t *req )
{
    char response[256] = {0};
    
    sprintf( response, "{\"status\":\"invalid mode!\"}" );
    
    httpd_resp_set_type( req,"application/json" );
    httpd_resp_send( req, response, strlen(response) ); 
    
    ESP_LOGI( TAG, "Request invalid" );

    return ESP_FAIL;
}

static esp_err_t xhr_set_wifi_sta( httpd_req_t *req, char *buffer )
{
    char response[512] = {0};
    char sta_ssid[MAX_SSID_LENGTH], sta_ssid_decoded[MAX_SSID_LENGTH] = {0}; 
    char sta_passphrase[MAX_PASS_LENGTH], sta_passphrase_decoded[MAX_PASS_LENGTH] = {0}; 

    if (httpd_query_key_value(buffer, "sta_ssid", sta_ssid, sizeof(sta_ssid)) == ESP_OK) {}
    example_uri_decode(sta_ssid_decoded, sta_ssid, strnlen(sta_ssid, MAX_SSID_LENGTH));

    if (httpd_query_key_value(buffer, "sta_passphrase", sta_passphrase, sizeof(sta_passphrase)) == ESP_OK) {}
    example_uri_decode(sta_passphrase_decoded, sta_passphrase, strnlen(sta_passphrase, MAX_PASS_LENGTH));

    config_set_sta_ssid( sta_ssid_decoded );
    config_set_sta_passphrase( sta_passphrase_decoded );
    config_set_sta_initialized();
    write_config();

    sprintf(response, "{\"data\":{\"sta_ssid\":\"%s\", \"sta_passphrase\":\"%s\"}, \"status\":{\"flag\":\"success\", \"message\":\"Wifi AP SSID changed, device will reboot now\"}}", 
        sta_ssid_decoded, sta_passphrase_decoded 
    );
    
    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, response, strlen(response)); 
    
    // reboot the device, so AP changed take affect
    queue_reboot();

    ESP_LOGI(TAG, "Set wifi station data");

    return ESP_OK;
}

static esp_err_t xhr_set_server_ip( httpd_req_t *req, char *buffer )
{
    char response[256] = {0};
    char server_ip[32], server_ip_decoded[32] = {0}; 

    if (httpd_query_key_value(buffer, "server_ip", server_ip, sizeof(server_ip)) == ESP_OK) {}
    example_uri_decode(server_ip_decoded, server_ip, strnlen(server_ip, 32));

    config_set_server_adress( server_ip_decoded );
    config_set_server_initialized();
    write_config();
    
    ESP_LOGI(TAG, "Set server ip: %s", server_ip_decoded);

    snprintf(response, sizeof(response), "{\"data\":{\"server_ip\":\"%s\"}, \"status\":{\"flag\":\"success\", \"message\":\"Set the server IP\"}}", server_ip_decoded );
    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, response, strlen(response)); 

    // reboot the device, so AP changed take affect
    queue_reboot();

    return ESP_OK;
}

static esp_err_t xhr_handler( httpd_req_t *req )
{
    char    *buffer;
    esp_err_t esp_state = ESP_FAIL;
    char mode[32] = {0};

    if ( req->content_len <= 0 )
        return esp_state;

    buffer = malloc(req->content_len + 32);
    if ( !buffer )
        return esp_state;

    int recv_len = httpd_req_recv( req, buffer, req->content_len );
    if ( recv_len <= 0)
    {
        free(buffer);
        return esp_state;
    }
    buffer[recv_len] = '\0';

    // retrieve the operation mode
    if (httpd_query_key_value(buffer, "mode", mode, sizeof(mode)) != ESP_OK) {
        esp_state = xhr_invalid_mode( req );
        free(buffer);
        return esp_state;
    }

    // operation mode dispatching
    if (strstr(mode, "set_wifi_ap") != NULL) 
    {
        esp_state = xhr_set_wifi_sta( req, buffer );
    }
    else if (strstr(mode, "set_server_ip") != NULL) 
    {
        esp_state = xhr_set_server_ip( req, buffer );
    }
    else{
        esp_state = xhr_invalid_mode( req );
    }

    free(buffer);
    return esp_state;
}

static esp_err_t get_config_handler( httpd_req_t *req )
{
    static const char *false_true[] = {"false", "true"};

    char *json_config = heap_caps_malloc( 1024, MALLOC_CAP_8BIT );

    config_t *config = get_config();

    snprintf( json_config, 1024, "{\"sta_initialized\": %s, \"sta_ssid\":\"%s\", \"sta_passphrase\":\"%s\", \"server_initialized\": %s, \"server_address\":\"%s\"}", 
        false_true[config->sta_initialized], 
        config->sta_ssid, 
        config->sta_passphrase,
        false_true[config->server_initialized], 
        config->server_address
    );

    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, json_config, strlen(json_config)); 
    heap_caps_free(json_config);
        
    ESP_LOGI(TAG, "Request config");

    return ESP_OK;
}

static esp_err_t get_factory_reset_handler( httpd_req_t *req )
{
    set_factory_config();
    write_config();

    char response[256] = {0};
    snprintf(response, sizeof(response), "{\"data\":{}, \"status\":{\"flag\":\"success\", \"message\":\"Device is reset\"}}" );
    
    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, response, strlen(response)); 

    ESP_LOGI(TAG, "Request Factory Reset");

    // reboot the device
    queue_reboot();

    return ESP_OK;    
}

static esp_err_t reboot_device_handler( httpd_req_t *req )
{
    char response[256] = {0};
    snprintf(response, sizeof(response), "{\"data\":{}, \"status\":{\"flag\":\"success\", \"message\":\"Device is rebooting\"}}" );
    
    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, response, strlen(response)); 

    ESP_LOGI(TAG, "Reboot device");

    // reboot the device
    queue_reboot();

    return ESP_OK;    
}

static esp_err_t disable_ap_handler( httpd_req_t *req )
{
    update_wifi_ap_mode( false );
    
    char response[256] = {0};
    snprintf(response, sizeof(response), "{\"data\":{}, \"status\":{\"flag\":\"success\", \"message\":\"AP is disabling\"}}" );
    
    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, response, strlen(response)); 

    ESP_LOGI(TAG, "Manually disabling AP");

    return ESP_OK;    
}

static esp_err_t find_ap_handler( httpd_req_t *req )
{

    // async: fist request will not return a list
    ap_scan_dispatch_async();

    uint16_t *scan_ap_count = get_scan_ap_count();

    char ap_name[128] = { 0 };
    char aps[1024] = { 0 };
    char *aps_ptr = aps;

    uint16_t i = 0;
    uint16_t *ap_count = get_scan_ap_count();
    wifi_ap_list_t *ap_data = get_scan_ap_data();

    size_t total_ssid_len = 0;

    for ( i = 0; i < *ap_count; i++ )
    {
        sprintf( ap_name, "\"%s\"", ap_data[i].ssid);
        size_t ssid_len = strlen(ap_name);

        memcpy(  aps_ptr, ap_name, ssid_len );
        
        ESP_LOGI(TAG, "%s", ap_data[i].ssid);

        aps_ptr += ssid_len;

        if ( i < (*ap_count-1) )
        {
            memcpy(  aps_ptr, ",", ssid_len );
            aps_ptr += 1; // todo: add too ssid_len
        }

        total_ssid_len += ssid_len;
    }
    aps_ptr = "\0";

    char *response = malloc(sizeof(char) * (total_ssid_len + 256));

    sprintf(response, "{\"data\":{\"num_ap\": %d, \"aps\": [%s]}, \"status\":{\"flag\":\"success\", \"message\":\"List of APs\"}}",
        *scan_ap_count,
        aps
    );
    
    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, response, strlen(response)); 

    ESP_LOGI(TAG, "Request AP list");

    return ESP_OK;    
}

static uint16_t     num_uri_handlers;
static httpd_uri_t  uri_handlers[7];

#define ADD_URI_HANDLER( _method, _uri, _handler ) \
    uri_handlers[num_uri_handlers++] = (httpd_uri_t){ \
        .uri       = _uri, \
        .method    = _method, \
        .handler   = _handler, \
        .user_ctx  = NULL \
    };

static void define_endpoints( void )
{
    num_uri_handlers = 0;

    ADD_URI_HANDLER( HTTP_GET,      "/",                root_get_handler )
    ADD_URI_HANDLER( HTTP_POST,     "/xhr",             xhr_handler )
    ADD_URI_HANDLER( HTTP_GET,      "/get_config",      get_config_handler )
    ADD_URI_HANDLER( HTTP_GET,      "/factory_reset",   get_factory_reset_handler )
    ADD_URI_HANDLER( HTTP_GET,      "/find_ap",         find_ap_handler )
    ADD_URI_HANDLER( HTTP_GET,      "/reboot_device",   reboot_device_handler )
    ADD_URI_HANDLER( HTTP_GET,      "/disable_ap",      disable_ap_handler )

    // assets
    ADD_URI_HANDLER( HTTP_GET,      "/style.css",       style_css_get_handler )

}

httpd_handle_t init_webserver( void )
{
    uint16_t i;
    httpd_config_t config = HTTPD_DEFAULT_CONFIG();
    httpd_handle_t server = NULL;

    define_endpoints();

    if (httpd_start(&server, &config) == ESP_OK) 
    {
        for ( i = 0; i < num_uri_handlers; i ++ )
        {
            httpd_uri_t *handler = &uri_handlers[i];
            httpd_register_uri_handler( server, handler );
        }
    }

    return server;
}