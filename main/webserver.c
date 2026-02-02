#include "common.h"
#include "config.h"
#include "wifi.h"

#include "esp_http_server.h"
#include <esp_log.h>

// https://github.com/espressif/arduino-esp32/blob/master/tools/partitions/min_spiffs.csv
// https://github.com/espressif/esp-idf/blob/master/examples/protocols/http_server/simple/main/main.c

#define HTTP_QUERY_KEY_MAX_LEN  64

static const char *TAG = "webserver";

#define PAGE_STA_SETUP      0
#define PAGE_SERVER_SETUP   1
#define PAGE_OVERVIEW       2

static const char *webpages[3] = {
    "/spiffs/web/sta_setup.html",
    "/spiffs/web/server_setup.html",
    "/spiffs/web/overview.html",
};

static const char *get_root_html_path( config_t *config )
{
    // get the html path based on current configuration
    // first-boot:      sta setup
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
    char response[256] = {0};
    char sta_ssid[32] = {0}; 
    char sta_passphrase[16] = {0}; 

    if (httpd_query_key_value(buffer, "sta_ssid", sta_ssid, sizeof(sta_ssid)) == ESP_OK) {}
    if (httpd_query_key_value(buffer, "sta_passphrase", sta_passphrase, sizeof(sta_passphrase)) == ESP_OK) {}

    config_set_sta_ssid( sta_ssid );
    config_set_sta_passphrase( sta_passphrase );
    config_set_sta_initialized();
    write_config();

    sprintf(response, "{\"data\":{\"sta_ssid\":\"%s\", \"sta_passphrase\":\"%s\"}, \"status\":{\"flag\":\"success\", \"message\":\"Wifi AP SSID changed, device will reboot now\"}}", sta_ssid, sta_passphrase );
    
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
    char server_ip[32] = {0}; 

    if (httpd_query_key_value(buffer, "server_ip", server_ip, sizeof(server_ip)) == ESP_OK) {}

    config_set_server_adress( server_ip );
    config_set_server_initialized();
    write_config();
    
    ESP_LOGI(TAG, "Set server ip: %s", server_ip);

    sprintf(response, "{\"data\":{\"server_ip\":\"%s\"}, \"status\":{\"flag\":\"success\", \"message\":\"Set the server IP\"}}", server_ip );
    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, response, strlen(response)); 

    return ESP_OK;
}

static esp_err_t xhr_handler( httpd_req_t *req )
{
    char    *buffer;
    esp_err_t esp_state = ESP_FAIL;
    char mode[32] = {0};

    if ( req->content_len <= 0 )
        return esp_state;

    buffer = malloc(req->content_len);
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

    char json_config[256] = { 0 };

    config_t *config = get_config();

    sprintf( json_config, "{\"sta_initialized\": %s, \"sta_ssid\":\"%s\", \"sta_passphrase\":\"%s\", \"server_initialized\": %s, \"server_address\":\"%s\"}", 
        false_true[config->sta_initialized], 
        config->sta_ssid, 
        config->sta_passphrase,
        false_true[config->server_initialized], 
        config->server_address
    );

    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, json_config, strlen(json_config)); 
    
    ESP_LOGI(TAG, "Request config");

    return ESP_OK;
}

static esp_err_t get_factory_reset_handler( httpd_req_t *req )
{
    set_factory_config();
    write_config();

    char response[256] = {0};
    sprintf(response, "{\"data\":{}, \"status\":{\"flag\":\"success\", \"message\":\"Device is reset\"}}" );
    
    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, response, strlen(response)); 

    ESP_LOGI(TAG, "Request Factory Reset");

    // reboot the device
    queue_reboot();

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

static httpd_uri_t uri_root = {
    .uri       = "/",
    .method    = HTTP_GET,
    .handler   = root_get_handler,
    .user_ctx  = NULL
};

static httpd_uri_t uri_xhr = {
    .uri       = "/xhr",
    .method    = HTTP_POST,
    .handler   = xhr_handler,
    .user_ctx  = NULL
};

static httpd_uri_t uri_get_config = {
    .uri       = "/get_config",
    .method    = HTTP_GET,
    .handler   = get_config_handler,
    .user_ctx  = NULL
};

static httpd_uri_t uri_factory_reset = {
    .uri       = "/factory_reset",
    .method    = HTTP_GET,
    .handler   = get_factory_reset_handler,
    .user_ctx  = NULL
};

static httpd_uri_t uri_find_ap = {
    .uri       = "/find_ap",
    .method    = HTTP_GET,
    .handler   = find_ap_handler,
    .user_ctx  = NULL
};

httpd_handle_t init_webserver( void )
{
    httpd_config_t config = HTTPD_DEFAULT_CONFIG();
    httpd_handle_t server = NULL;

    if (httpd_start(&server, &config) == ESP_OK) 
    {
        httpd_register_uri_handler( server, &uri_root );
        httpd_register_uri_handler( server, &uri_xhr );
        httpd_register_uri_handler( server, &uri_get_config );
        httpd_register_uri_handler( server, &uri_factory_reset );
        httpd_register_uri_handler( server, &uri_find_ap );
    }

    return server;
}