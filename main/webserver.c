#include <stdio.h>
#include "freertos/FreeRTOS.h"
#include "freertos/task.h"
#include "esp_http_server.h"

#include "common.h"
//#include "cJSON.h"

// https://github.com/espressif/arduino-esp32/blob/master/tools/partitions/min_spiffs.csv
// https://github.com/espressif/esp-idf/blob/master/examples/protocols/http_server/simple/main/main.c

#define HTTP_QUERY_KEY_MAX_LEN  64

static esp_err_t root_get_handler( httpd_req_t *req )
{
    FILE *file = fopen("/spiffs/web/index.html", "r");
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

static esp_err_t xhr_test( httpd_req_t *req, char *buffer )
{
    char response[256] = {0};
    char ssid[32] = {0}; 

    if (httpd_query_key_value(buffer, "SSID", ssid, sizeof(ssid)) == ESP_OK) {}

    sprintf(response, "{\"data\":{\"SSID\":\"%s\"}}", ssid );
    
    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, response, strlen(response)); 
    
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
    if (strstr(mode, "set_wifi") != NULL) 
    {
        esp_state = xhr_test( req, buffer );
    }
    else if (strstr(mode, "set_led") != NULL) 
    {
        esp_state = xhr_led( req, buffer );
    }

    free(buffer);
    return esp_state;
}

static httpd_uri_t root = {
    .uri       = "/",
    .method    = HTTP_GET,
    .handler   = root_get_handler,
    .user_ctx  = NULL
};

static httpd_uri_t xhr = {
    .uri       = "/xhr",
    .method    = HTTP_POST,
    .handler   = xhr_handler,
    .user_ctx  = NULL
};

httpd_handle_t init_webserver( void )
{
    httpd_config_t config = HTTPD_DEFAULT_CONFIG();
    httpd_handle_t server = NULL;

    if (httpd_start(&server, &config) == ESP_OK) 
    {
        httpd_register_uri_handler( server, &root );
        httpd_register_uri_handler( server, &xhr );
    }

    return server;
}