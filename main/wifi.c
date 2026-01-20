#include <freertos/FreeRTOS.h>
#include <freertos/event_groups.h>
#include <esp_wifi.h>
#include <esp_event.h>
#include <esp_log.h>
#include <string.h>

#include <nvs_flash.h>

static void nvs_init( void )
{
    esp_err_t ret = nvs_flash_init();
    if (ret == ESP_ERR_NVS_NO_FREE_PAGES || ret == ESP_ERR_NVS_NEW_VERSION_FOUND)
    {
        ESP_ERROR_CHECK(nvs_flash_erase());
        ret = nvs_flash_init();
    }
    ESP_ERROR_CHECK(ret);
}

static const char *TAG = "wifi_ap";

static void event_handler(void* arg, esp_event_base_t event_base,
                               int32_t event_id, void* event_data)
{
    if (event_base == WIFI_EVENT) {
        switch(event_id) {
            case WIFI_EVENT_AP_STACONNECTED: {
                wifi_event_ap_staconnected_t* event = (wifi_event_ap_staconnected_t*) event_data;
                ESP_LOGI(TAG, "Station connected, AID=%d", event->aid);
                break;
            }
            case WIFI_EVENT_AP_STADISCONNECTED: {
                wifi_event_ap_stadisconnected_t* event = (wifi_event_ap_stadisconnected_t*) event_data;
                ESP_LOGI(TAG, "Station disconnected, AID=%d", event->aid);
                break;
            }
            default:
                break;
        }
    }
}

void init_wifi_ap( void )
{
    // https://github.com/HankB/ESP32-ESP-IDF-PlatformIO-start/blob/C%2B%2B/src/wifi.cpp

    nvs_init();

    ESP_ERROR_CHECK(esp_netif_init());

    ESP_ERROR_CHECK(esp_event_loop_create_default());
    
    esp_netif_create_default_wifi_ap();

    wifi_init_config_t cfg = WIFI_INIT_CONFIG_DEFAULT();
    ESP_ERROR_CHECK(esp_wifi_init(&cfg));

    // event handler
    ESP_ERROR_CHECK(esp_event_handler_instance_register(WIFI_EVENT,
                                                        ESP_EVENT_ANY_ID,
                                                        &event_handler,
                                                        NULL,
                                                        NULL));

    const char *ap_name = "GreatestWIFI";
    const char *ap_pass = "12345678";

    wifi_config_t wifi_ap_config = {0};

    strncpy((char *)wifi_ap_config.ap.password, ap_pass, sizeof(wifi_ap_config.ap.password));
    strncpy((char *)wifi_ap_config.ap.ssid, ap_name, sizeof(wifi_ap_config.ap.ssid));
    wifi_ap_config.ap.ssid_len = strlen(ap_name);

    wifi_ap_config.ap.max_connection = 4;
    wifi_ap_config.ap.channel = 1;
    wifi_ap_config.ap.authmode = WIFI_AUTH_WPA2_PSK;

    ESP_ERROR_CHECK(esp_wifi_set_mode(WIFI_MODE_AP));
    ESP_ERROR_CHECK(esp_wifi_set_config(WIFI_IF_AP, &wifi_ap_config));
    ESP_ERROR_CHECK(esp_wifi_start());

    ESP_LOGI(TAG, "Wi-Fi AP started. SSID:%s Password:%s",
             wifi_ap_config.ap.ssid, wifi_ap_config.ap.password);                                                   
}