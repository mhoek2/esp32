#include "common.h"
#include "config.h"

#include "esp_http_server.h"
#include "driver/temperature_sensor.h"

// https://github.com/espressif/arduino-esp32/blob/master/tools/partitions/min_spiffs.csv
// https://github.com/espressif/esp-idf/blob/master/examples/protocols/http_server/simple/main/main.c

#define HTTP_QUERY_KEY_MAX_LEN  64

static esp_err_t root_get_handler( httpd_req_t *req )
{
    config_t *config = get_config();

    const char *path[2] = {
        "/spiffs/web/setup.html",
        "/spiffs/web/index.html"
    };

    FILE *file = fopen( path[config->initialized], "r" );

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
    return ESP_OK;
}

static esp_err_t xhr_invalid_mode( httpd_req_t *req )
{
    char response[256] = {0};
    
    sprintf(response, "{\"status\":\"invalid mode!\"}" );
    
    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, response, strlen(response)); 
    
    return ESP_FAIL;
}

static esp_err_t xhr_set_wifi_AP( httpd_req_t *req, char *buffer )
{
    char response[256] = {0};
    char ap_ssid[32] = {0}; 
    char ap_passphrase[16] = {0}; 

    if (httpd_query_key_value(buffer, "ap_ssid", ap_ssid, sizeof(ap_ssid)) == ESP_OK) {}
    if (httpd_query_key_value(buffer, "ap_passphrase", ap_passphrase, sizeof(ap_passphrase)) == ESP_OK) {}

    config_set_ap_ssid( ap_ssid );
    config_set_ap_passphrase( ap_passphrase );
    config_set_initialized();
    write_config();

    // temporary
    float temperature = get_temp();

    sprintf(response, "{\"data\":{\"ap_ssid\":\"%s\", \"ap_passphrase\":\"%s\", \"temp\": %f}, \"status\":{\"flag\":\"success\", \"message\":\"Wifi AP SSID changed, device will reboot now\"}}", ap_ssid, ap_passphrase, temperature );
    
    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, response, strlen(response)); 
    
    // reboot the device, so AP changed take affect
    queue_reboot();

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

    sprintf( json_config, "{\"initialized\": %s, \"ap_ssid\":\"%s\", \"ap_passphrase\":\"%s\"}", 
        false_true[config->initialized], 
        config->ap_ssid, 
        config->ap_passphrase 
    );

    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, json_config, strlen(json_config)); 
    
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


httpd_handle_t init_webserver( void )
{
    httpd_config_t config = HTTPD_DEFAULT_CONFIG();
    httpd_handle_t server = NULL;

    if (httpd_start(&server, &config) == ESP_OK) 
    {
        httpd_register_uri_handler( server, &uri_root );
        httpd_register_uri_handler( server, &uri_xhr );
        httpd_register_uri_handler( server, &uri_get_config );
    }

    return server;
}