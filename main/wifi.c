#include "common.h"
#include "config.h"
#include "wifi.h"
#include "webclient.h"

#include <freertos/event_groups.h>
#include <esp_wifi.h>
#include <esp_event.h>
#include <esp_log.h>
#include "esp_timer.h"

#include <nvs_flash.h>

static const char *TAG = "wifi_ap";

#define AP_LIST_MAX 32

static uint16_t         scan_ap_count = 0;
static wifi_ap_list_t   *scan_ap_data;
static bool             scanning_ap = false;
static const char       *wifi_type_str[WIFI_TYPES_MAX] = {
    "STA",
    "AP",
};

wifi_state_t wifi_state[WIFI_TYPES_MAX] = { 0 };

// global
int64_t get_wifi_enabled_time( wifi_mode_type_t type )
{
    if ( type >= WIFI_TYPES_MAX )
        return 0;
 
    return wifi_state[type].enabled_time;
}

void reset_wifi_enabled_time( wifi_mode_type_t type )
{
    if ( type >= WIFI_TYPES_MAX )
        return;

    wifi_state[type].enabled_time = esp_timer_get_time();

    ESP_LOGW( TAG, "Reset timer for WiFi type [%s]", wifi_type_str[type] );
}

bool get_wifi_enabled( wifi_mode_type_t type )
{
    if ( type >= WIFI_TYPES_MAX )
        return false;

    return wifi_state[type].enabled;
}

bool wifi_timer_hit( wifi_mode_type_t type )
{
    if ( type >= WIFI_TYPES_MAX )
        return false;

    return (esp_timer_get_time() - wifi_state[type].enabled_time) > wifi_state[type].time_limit;
}

// AP
uint16_t *get_scan_ap_count( void )
{
    return &scan_ap_count;
}

wifi_ap_list_t *get_scan_ap_data( void )
{
    return scan_ap_data;
}

// wifi connect
static esp_timer_handle_t wifi_connect_timer;
static void wifi_connect( void *arg )
{
    config_t *config = get_config();

    ESP_LOGW( TAG, "Wi-Fi STA Connecting to: SSID:%s Password:%s",
             config->sta_ssid, 
             config->sta_passphrase 
    );
    esp_wifi_connect();
}

void queue_wifi_connect( void )
{
    esp_timer_start_once( wifi_connect_timer, 5000 * 1000 );
}

void init_wifi_connnect_timer( void )
{
    const esp_timer_create_args_t args = {
        .callback   = &wifi_connect,
        .name       = "wifi_connect"
    };

    ESP_ERROR_CHECK( esp_timer_create( &args, &wifi_connect_timer ) );
}

// https://esp32.com/viewtopic.php?t=10619#p45808
#define CONFIG_AP_SSID "ESP32-Device"
#define CONFIG_AP_PASS "1357913579"
#define CONFIG_AP_CHAN 3

wifi_country_t country = {
    .cc = "EU", 
    .schan = 1,
    .nchan = 11,
    .policy = WIFI_COUNTRY_POLICY_AUTO
};

static void event_handler(void* arg, esp_event_base_t event_base,
                               int32_t event_id, void* event_data)
{
    if (event_base == WIFI_EVENT) {
        ESP_LOGI(TAG, "event triggerd: %d", event_id );

        switch(event_id) {
            case WIFI_EVENT_STA_CONNECTED: {
                wifi_event_sta_connected_t* event = (wifi_event_sta_connected_t*) event_data;
                ESP_LOGI(TAG, "STA connected, AID=%d", event->aid);

                // synchronize all device sensors and states
                webclient_register_device();
                webclient_update_windowstate();
                //webclient_update_sleep();

                break;
            }
            case WIFI_EVENT_STA_DISCONNECTED: {
                wifi_event_sta_disconnected_t* event = (wifi_event_sta_disconnected_t*) event_data;
                ESP_LOGI(TAG, "STA disconnected, reason=%d", event->reason);

                // reconnect when STA is not in sleep mode
                if ( !wifi_timer_hit( WIFI_TYPE_STA ) )
                    queue_wifi_connect();

                break;
            }
            case WIFI_EVENT_AP_STACONNECTED: {
                wifi_event_ap_staconnected_t* event = (wifi_event_ap_staconnected_t*) event_data;
                ESP_LOGI(TAG, "AP connected, AID=%d", event->aid);
                break;
            }
            case WIFI_EVENT_AP_STADISCONNECTED: {
                wifi_event_ap_stadisconnected_t* event = (wifi_event_ap_stadisconnected_t*) event_data;
                ESP_LOGI(TAG, "AP disconnected, AID=%d", event->aid);
                break;
            }
            case WIFI_EVENT_SCAN_DONE: {

                // scan alraedy in progress
                if ( scanning_ap )
                    break;
                 
                scanning_ap = true;
                
                ESP_ERROR_CHECK( esp_wifi_scan_get_ap_num( &scan_ap_count ) );

                if ( scan_ap_count <= 0 )
                {
                    scanning_ap = false;
                    break;
                }

                wifi_ap_record_t *ap_records = malloc(sizeof(wifi_ap_record_t) * scan_ap_count);
                ESP_ERROR_CHECK( esp_wifi_scan_get_ap_records( &scan_ap_count, ap_records ) );

                wifi_ap_list_t *data;

                ESP_LOGI(TAG, "Num AP found: %d", scan_ap_count);

                for ( uint32_t i = 0; i < scan_ap_count; i++ ) 
                {
                    if ( i >= AP_LIST_MAX )
                        break;

                    data = &scan_ap_data[i];

                    ESP_LOGI(TAG, "%s", data->ssid);
                    memcpy( data->ssid, ap_records[i].ssid, sizeof(ap_records[i].ssid) );
                }

                free( ap_records );

                scanning_ap = false;
                break;
            }
            case WIFI_EVENT_AP_START: {
                wifi_state[WIFI_TYPE_AP].enabled = true;
                reset_wifi_enabled_time( WIFI_TYPE_AP );
                ESP_LOGI( TAG, "enabled AP" );
                break;
            }
            case WIFI_EVENT_AP_STOP: {
                wifi_state[WIFI_TYPE_AP].enabled = false;
                ESP_LOGI( TAG, "Disabled AP" );
                break;
            }
            case WIFI_EVENT_STA_START: {
                ESP_LOGI(TAG, "Start STA");
                reset_wifi_enabled_time( WIFI_TYPE_STA );
                wifi_state[WIFI_TYPE_STA].enabled = true;
 
                config_t *config = get_config();
                if ( config->sta_initialized )
                {
                    queue_wifi_connect();
                }

                break;
            }
            case WIFI_EVENT_STA_STOP: {
                wifi_state[WIFI_TYPE_STA].enabled = false;
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
    // scan alraedy in progress
    if ( scanning_ap )
        return;

    wifi_scan_config_t scan_config = {
        .ssid = NULL,
        .bssid = NULL,
        .channel = 0,
        .show_hidden = false,
        .scan_type   = WIFI_SCAN_TYPE_ACTIVE,
        .scan_time = {
            .active.min = 100,
            .active.max = 1500,
        }
    };

    ESP_ERROR_CHECK( esp_wifi_scan_start( &scan_config, false ) );
}

void wifi_sta_configure( void )
{
    config_t *config = get_config();

    wifi_config_t wifi_sta_config = {
        .sta = {
            .threshold.authmode = WIFI_AUTH_WPA2_PSK,
            .pmf_cfg = {
                .capable = true,
                .required = false
            }
        }
    };

    memcpy( wifi_sta_config.sta.ssid, config->sta_ssid, sizeof(config->sta_ssid) );
    memcpy( wifi_sta_config.sta.password, config->sta_passphrase, sizeof(config->sta_passphrase) );

    ESP_ERROR_CHECK( esp_wifi_set_config( WIFI_IF_STA, &wifi_sta_config ) ) ;

    ESP_LOGI( TAG, "Wi-Fi STA. SSID:%s Password:%s",
             wifi_sta_config.sta.ssid, 
             wifi_sta_config.sta.password 
    );

    init_wifi_connnect_timer();

    if ( config->sta_initialized )
    {
        queue_wifi_connect();
    }
}

void wifi_ap_configure( void )
{
    //if (strlen((char *)wifi_ap_config.ap.password) == 0) {
    //    wifi_ap_config.ap.authmode = WIFI_AUTH_OPEN;
    //}

    wifi_config_t wifi_ap_config = {
        .ap = {
            .ssid = CONFIG_AP_SSID,
            .ssid_len = strlen(CONFIG_AP_SSID),
            .password = CONFIG_AP_PASS,
            .channel = CONFIG_AP_CHAN,
            .max_connection = 4,
            .authmode = WIFI_AUTH_WPA_WPA2_PSK
        }
    };
    if (strlen(CONFIG_AP_PASS) == 0) {
        wifi_ap_config.ap.authmode = WIFI_AUTH_OPEN;
    }

    ESP_ERROR_CHECK( esp_wifi_set_config( WIFI_IF_AP, &wifi_ap_config ) ) ;

    ESP_LOGI( TAG, "Wi-Fi AP Configured. SSID:%s Password:%s",
             wifi_ap_config.ap.ssid, 
             wifi_ap_config.ap.password
    );    
}

static void wifi_ap_enable( void )
{
    if ( get_wifi_enabled(WIFI_TYPE_STA) )
    {
        ESP_ERROR_CHECK(esp_wifi_set_mode(WIFI_MODE_APSTA));
        ESP_LOGI( TAG, "Enabling AP keep STA on" );
    }
    else
    {
        ESP_ERROR_CHECK(esp_wifi_set_mode(WIFI_MODE_AP));
        ESP_LOGI( TAG, "Enabling AP keep STA off" );
    }
}

static void wifi_ap_disable( void )
{
    if ( get_wifi_enabled(WIFI_TYPE_STA) )
    {
        ESP_ERROR_CHECK( esp_wifi_set_mode ( WIFI_MODE_STA ) );
        ESP_LOGI( TAG, "Disabling AP keep STA on" );
    }
    else
    {
        ESP_ERROR_CHECK( esp_wifi_set_mode ( WIFI_MODE_NULL ) );
        ESP_LOGI( TAG, "Disabling STA (all)" );
    }
}

void update_wifi_ap_mode( bool use_ap )
{
    if ( use_ap ) 
    {
        wifi_ap_enable();
    } 

    else
    {
        wifi_ap_disable();
    }
}

static void wifi_sta_enable( void )
{
    ESP_LOGI( TAG, "Enabling AP" );

    //ESP_ERROR_CHECK(esp_wifi_set_mode(WIFI_MODE_AP));

    if ( get_wifi_enabled(WIFI_TYPE_AP) )
    {
        ESP_ERROR_CHECK(esp_wifi_set_mode(WIFI_MODE_APSTA));
        ESP_LOGI( TAG, "Enabling STA keep AP on" );
    }
    else
    {
        ESP_ERROR_CHECK(esp_wifi_set_mode(WIFI_MODE_STA));
        ESP_LOGI( TAG, "Enabling STA keep AP off" );
    }
}

static void wifi_sta_disable( void )
{
    // first need to make sure 'sleep' member of devices table
    // is updated (not implemented yet)

    if ( get_wifi_enabled(WIFI_TYPE_AP) )
    {
        ESP_ERROR_CHECK( esp_wifi_set_mode ( WIFI_MODE_AP ) );
        ESP_LOGI( TAG, "Disabling STA keep AP on" );
    }
    else
    {
        ESP_ERROR_CHECK( esp_wifi_set_mode ( WIFI_MODE_NULL ) );
        ESP_LOGI( TAG, "Disabling STA (all)" );
    }
}

void update_wifi_sta_mode( bool use_sta )
{
    if ( use_sta ) 
    {
        wifi_sta_enable();
    } 

    else
    {
        wifi_sta_disable();
    }
}

static void init_wifi_timers( void )
{
    wifi_state[WIFI_TYPE_STA].time_limit = DISABLE_STA_AFTER;
    wifi_state[WIFI_TYPE_AP].time_limit = DISABLE_AP_AFTER;
}

void init_wifi( void )
{
    // https://github.com/HankB/ESP32-ESP-IDF-PlatformIO-start/blob/C%2B%2B/src/wifi.cpp

    init_wifi_timers();

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

    //ESP_ERROR_CHECK( esp_wifi_set_mode( WIFI_MODE_STA ) );

    // always start with AP enabled
    update_wifi_sta_mode( true );
    wifi_state[WIFI_TYPE_STA].enabled = true;
    update_wifi_ap_mode( true );

    wifi_sta_configure();
    wifi_ap_configure();

    //ESP_ERROR_CHECK( esp_wifi_set_country( &country ) );
    ESP_ERROR_CHECK( esp_wifi_start() );
    ESP_ERROR_CHECK( esp_wifi_set_ps( WIFI_PS_NONE ) );                                         
}

void destroy_wifi( void )
{
    free( scan_ap_data );
}