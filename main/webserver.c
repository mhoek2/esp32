#include "common.h"
#include "config.h"
#include "wifi.h"

#include "esp_http_server.h"
#include <esp_log.h>

// https://github.com/espressif/arduino-esp32/blob/master/tools/partitions/min_spiffs.csv
// https://github.com/espressif/esp-idf/blob/master/examples/protocols/http_server/simple/main/main.c

#define HTTP_QUERY_KEY_MAX_LEN  64

static const char *TAG = "webserver";

static esp_err_t root_get_handler( httpd_req_t *req )
{
    config_t *config = get_config();

    const char *path[2] = {
        "/spiffs/web/sta_setup.html",
        "/spiffs/web/server_setup.html"
    };

    FILE *file = fopen( path[config->sta_initialized], "r" );

    if (!file) {
        httpd_resp_send_500(req);
        return ESP_FAIL;
    }

    //{{%cpu_temp%}}  replace 

    char buffer[1024];
    size_t read_bytes;
    while ((read_bytes = fread(buffer, 1, sizeof(buffer), file)) > 0) {
        httpd_resp_send_chunk(req, buffer, read_bytes);
    }

    fclose(file);
    httpd_resp_send_chunk(req, NULL, 0);


    ESP_LOGI(TAG, "Request html");

    return ESP_OK;
}

static esp_err_t xhr_invalid_mode( httpd_req_t *req )
{
    char response[256] = {0};
    
    sprintf(response, "{\"status\":\"invalid mode!\"}" );
    
    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, response, strlen(response)); 
    
    ESP_LOGI(TAG, "Request invalid");

    return ESP_FAIL;
}

static esp_err_t xhr_set_wifi_AP( httpd_req_t *req, char *buffer )
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

static esp_err_t xhr_led( httpd_req_t *req, char *buffer )
{
    char response[256] = {0};
    char speed[32] = {0}; 

    if (httpd_query_key_value(buffer, "speed", speed, sizeof(speed)) == ESP_OK) {}

    sprintf(response, "{\"data\":{\"speed\":\"%s\"}}", speed );
    
    set_interval( atoi(speed) );
    
    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, response, strlen(response)); 
    
    ESP_LOGI(TAG, "Set led speed");

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
        esp_state = xhr_set_wifi_AP( req, buffer );
    }
    else if (strstr(mode, "set_led") != NULL) 
    {
        esp_state = xhr_led( req, buffer );
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

static esp_err_t get_reset_handler( httpd_req_t *req )
{
    set_factory_config();
    write_config();

    char response[256] = {0};
    sprintf(response, "{\"data\":{}, \"status\":{\"flag\":\"success\", \"message\":\"Device is reset\"}}" );
    
    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, response, strlen(response)); 

    ESP_LOGI(TAG, "Request Factory Reset");

    return ESP_OK;    
}

static esp_err_t find_ap_handler( httpd_req_t *req )
{

    uint16_t *scan_ap_count = get_scan_ap_count();

    char response[256] = {0};
    sprintf(response, "{\"data\":{\"num_ap\": %d}, \"status\":{\"flag\":\"success\", \"message\":\"List of APs\"}}",
        *scan_ap_count
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
    .uri       = "/reset",
    .method    = HTTP_GET,
    .handler   = get_reset_handler,
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