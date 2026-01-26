#include "common.h"
#include "wifi.h"

#include <freertos/event_groups.h>
#include <esp_wifi.h>
#include <esp_event.h>
#include <esp_log.h>

#include <nvs_flash.h>

//const char *ap_name = "GreatestWIFI";
//const char *ap_pass = "12345678";
//wifi_config_t wifi_ap_config = {0};
//strncpy((char *)wifi_ap_config.ap.password, ap_pass, sizeof(wifi_ap_config.ap.password));
//strncpy((char *)wifi_ap_config.ap.ssid, ap_name, sizeof(wifi_ap_config.ap.ssid));
//wifi_ap_config.ap.ssid_len = strlen(ap_name);

static const char *TAG = "wifi_ap";

#define AP_LIST_MAX 32

static uint16_t         scan_ap_count = 0;
static wifi_ap_list_t   *scan_ap_data;

static bool wifi_ap_enabled = false;

uint16_t *get_scan_ap_count( void )
{
    return &scan_ap_count;
}

wifi_ap_list_t *get_scan_ap_data( void )
{
    return scan_ap_data;
}

static wifi_config_t wifi_ap_config = {
    .ap = {
        .ssid = "GreatestWIFI",
        .ssid_len = 14,
        .password = "12345678",
        .channel = 0,
        .max_connection = 4,
        .authmode = WIFI_AUTH_WPA2_PSK
    }
};

wifi_country_t country = {
    .cc = "EU", 
    .schan = 1,
    .nchan = 11,
    .policy = WIFI_COUNTRY_POLICY_AUTO
};

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
            case WIFI_EVENT_SCAN_DONE: {
                ESP_ERROR_CHECK( esp_wifi_scan_get_ap_num( &scan_ap_count ) );

                wifi_ap_record_t *ap_records = malloc(sizeof(wifi_ap_record_t) * scan_ap_count);
                ESP_ERROR_CHECK( esp_wifi_scan_get_ap_records( &scan_ap_count, ap_records ) );

                wifi_ap_list_t *data;

                for ( uint32_t i = 0; i < scan_ap_count; i++ ) 
                {
                    if ( i >= AP_LIST_MAX )
                        break;

                    data = &scan_ap_data[i];

                    memcpy( data->ssid, ap_records[i].ssid, sizeof(ap_records[i].ssid) );
                }

                free(ap_records);
                break;
            }
            case WIFI_EVENT_AP_START: {
                ESP_LOGI(TAG, "Start AP");
                break;
            }
            case WIFI_EVENT_AP_STOP: {
                ESP_LOGI(TAG, "Stop AP");
                break;
            }
            case WIFI_EVENT_STA_START: {
                ESP_LOGI(TAG, "Start STA");
                break;
            }
            case WIFI_EVENT_STA_STOP: {
                ESP_LOGI(TAG, "Stop STA");
                break;
            }
            case WIFI_EVENT_WIFI_READY: {
                ESP_LOGI(TAG, "Wifi ready");
                break;
            }

            default:
                break;
        }
    }
}

// should only be called whenever AP is active.
// AP should only be active when user is tring to edit the config..
// using a physical button on the device, that actives the AP for 5-10 minutes
void ap_scan_dispatch_async( void )
{
    wifi_scan_config_t scan_config = {
        .ssid = NULL,
        .bssid = NULL,
        .channel = 0,
        .show_hidden = true
    };

    ESP_ERROR_CHECK( esp_wifi_scan_start( &scan_config, false ) );
}

void destroy_wifi( void )
{
    free( scan_ap_data );
}

void init_wifi( void )
{
    // https://github.com/HankB/ESP32-ESP-IDF-PlatformIO-start/blob/C%2B%2B/src/wifi.cpp

    nvs_init();


    scan_ap_data = malloc( sizeof(wifi_ap_list_t) * AP_LIST_MAX );


    ESP_ERROR_CHECK( esp_netif_init() );

    ESP_ERROR_CHECK( esp_event_loop_create_default() );
    
    esp_netif_create_default_wifi_sta();
    esp_netif_create_default_wifi_ap();

    wifi_init_config_t cfg = WIFI_INIT_CONFIG_DEFAULT();
    ESP_ERROR_CHECK( esp_wifi_init( &cfg ) );

    // event handler
    ESP_ERROR_CHECK( esp_event_handler_instance_register( WIFI_EVENT,
                                                        ESP_EVENT_ANY_ID,
                                                        &event_handler,
                                                        NULL,
                                                        NULL ) );

    ESP_ERROR_CHECK( esp_wifi_set_mode( WIFI_MODE_STA ) );

    wifi_ap_enable();
    wifi_ap_configure();
    
    ESP_ERROR_CHECK( esp_wifi_set_country( &country ) );
    ESP_ERROR_CHECK( esp_wifi_start() );
    
    ESP_LOGI( TAG, "Wi-Fi AP started. SSID:%s Password:%s",
             wifi_ap_config.ap.ssid, wifi_ap_config.ap.password );                                                   
}

void wifi_ap_configure( void )
{
    //if (strlen((char *)wifi_ap_config.ap.password) == 0) {
    //    wifi_ap_config.ap.authmode = WIFI_AUTH_OPEN;
    //}

    ESP_ERROR_CHECK( esp_wifi_set_config( WIFI_IF_AP, &wifi_ap_config ) ) ;
}

void wifi_ap_enable( void )
{
    ESP_LOGI(TAG, "Enable AP");

    ESP_ERROR_CHECK(esp_wifi_set_mode(WIFI_MODE_AP));
    //ESP_ERROR_CHECK(esp_wifi_set_mode(WIFI_MODE_APSTA));
}

void wifi_ap_disable( void )
{
    ESP_LOGI(TAG, "Disabe AP");

    ESP_ERROR_CHECK( esp_wifi_set_mode ( WIFI_MODE_STA ) );
}

void update_wifi_mode( bool use_ap )
{
    if ( use_ap && !wifi_ap_enabled ) 
    {
        wifi_ap_configure();
        wifi_ap_enable();
        wifi_ap_enabled = true;
    } 

    else if ( !use_ap && wifi_ap_enabled ) 
    {
        wifi_ap_disable();
        wifi_ap_enabled = false;
    }
}