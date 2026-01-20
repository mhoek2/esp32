#include <stdio.h>
#include "freertos/FreeRTOS.h"
#include "freertos/task.h"
#include "driver/gpio.h"
#include "sdkconfig.h"

#include "wifi.h"


#include "esp_http_server.h"

#define LED_GPIO 8
#define INTERVAL 500 / portTICK_PERIOD_MS

static uint8_t s_led_state = 0;









static esp_err_t root_get_handler(httpd_req_t *req)
{
    const char *html = "<!DOCTYPE html>"
                       "<html>"
                       "<head><title>ESP32 AP</title></head>"
                       "<body>"
                       "<h1>Hello from ESP32!</h1>"
                       "<p>Connected via AP</p>"
                       "</body>"
                       "</html>";

    httpd_resp_send(req, html, HTTPD_RESP_USE_STRLEN);
    return ESP_OK;
}

//static const char *TAG_HTTP = "HTTP_SERVER";
static httpd_uri_t root = {
    .uri       = "/",
    .method    = HTTP_GET,
    .handler   = root_get_handler,
    .user_ctx  = NULL
};

static httpd_handle_t start_webserver(void)
{
    httpd_config_t config = HTTPD_DEFAULT_CONFIG();
    httpd_handle_t server = NULL;

    if (httpd_start(&server, &config) == ESP_OK) {
        httpd_register_uri_handler(server, &root);
    }

    return server;
}

static void blink_led(void)
{
    gpio_set_level(LED_GPIO, s_led_state);
}

static void configure_led(void)
{
    gpio_reset_pin(LED_GPIO);
    gpio_set_direction(LED_GPIO, GPIO_MODE_OUTPUT);
}

void app_main(void)
{
    configure_led();
    init_wifi_ap();
    
    start_webserver();

    while (1) {
        blink_led();

        s_led_state = !s_led_state;
        vTaskDelay(INTERVAL);
    } 
}
