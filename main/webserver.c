#include <stdio.h>
#include "freertos/FreeRTOS.h"
#include "freertos/task.h"
#include "esp_http_server.h"

// https://github.com/espressif/arduino-esp32/blob/master/tools/partitions/min_spiffs.csv


static esp_err_t root_get_handler( httpd_req_t *req )
{
    FILE *file = fopen("/spiffs/web/index.html", "r");
    if (!file) {
        httpd_resp_send_500(req);
        return ESP_FAIL;
    }

    char buffer[1024];
    size_t read_bytes;
    while ((read_bytes = fread(buffer, 1, sizeof(buffer), file)) > 0) {
        httpd_resp_send_chunk(req, buffer, read_bytes);
    }

    fclose(file);
    httpd_resp_send_chunk(req, NULL, 0);
    return ESP_OK;
}

static esp_err_t xhr_get_handler( httpd_req_t *req )
{
    char response[256];
    sprintf(response, "{\"timestat\":{\"hours\":%d,\"minutes\":%d,\"seconds\":%d}}", 1, 1, 1);
    
    httpd_resp_set_type(req,"application/json");
    httpd_resp_send(req, response, strlen(response));

    return ESP_OK;
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
    .handler   = xhr_get_handler,
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