#include "common.h"
#include "config.h"
#include "wifi.h"
#include "webserver.h"
#include "webclient.h"
#include "filesystem.h"
#include <nvs_flash.h>

#include <esp_log.h>
#include "esp_timer.h"

static const char *TAG = "main";
static int interval = 3000;

// simple queue
static esp_timer_handle_t reboot_timer;

// gpio
#define BUTTON_GPIO GPIO_NUM_6
volatile bool button_6_event = false;
int64_t press_time = 0;
int64_t ap_enabled_time = 0;

static void reboot_device(void *arg)
{
    ESP_LOGW(TAG, "Rebooting now...");
    esp_restart();
}

void queue_reboot( void )
{
    esp_timer_start_once( reboot_timer, 2000 * 1000 );
}

void init_reboot_timer(void)
{
    const esp_timer_create_args_t args = {
        .callback   = &reboot_device,
        .name       = "reboot_device"
    };

    ESP_ERROR_CHECK( esp_timer_create( &args, &reboot_timer ) );
}

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

static void IRAM_ATTR button_isr_handler(void *arg)
{ 
    button_6_event = true;
}

void init_buttons(void)
{
    gpio_config_t io_conf = {
        .pin_bit_mask   = 1ULL << BUTTON_GPIO,
        .mode           = GPIO_MODE_INPUT,
        .pull_up_en     = GPIO_PULLUP_ENABLE,
        .pull_down_en   = GPIO_PULLDOWN_DISABLE,
        .intr_type      = GPIO_INTR_NEGEDGE
    };

    gpio_config( &io_conf );

    gpio_install_isr_service( 0 );
    gpio_isr_handler_add( BUTTON_GPIO, button_isr_handler, NULL );
}

uint64_t to_ms( int32_t seconds ) 
{
    return seconds * 1000000.0;
}

void app_main( void )
{
    nvs_init();
    init_filesystem();
    init_config();
    
    if ( read_config() == ESP_FAIL )
    {
        read_config();  // retry once, as fail-safe
    }

    init_wifi();
    init_webserver();
    init_webclient();

    init_buttons();
    init_reboot_timer();
    
    //config_t *config = get_config();

    while ( 1 ) 
    {
#if 0
        if ( button_6_event )
        {
            press_time = esp_timer_get_time(); // microseconds

            // enable AP for x minutes
            // only, when device is fully initialized
            if ( device_initialized() && !get_wifi_ap_mode() )
            {
                ap_enabled_time = esp_timer_get_time();
                update_wifi_ap_mode( true );
                ESP_LOGW( TAG, "Manually enable AP" );
            }

            ESP_LOGW( TAG, "pressed button 6" );
            button_6_event = false;
        }
#else
        if ( button_6_event )
        {
            webclient_register_device();

            ESP_LOGW( TAG, "pressed button 6" );
            button_6_event = false; 
        }
#endif
   
        // factory reset?
        if ( gpio_get_level( BUTTON_GPIO ) == 0 ) 
        {
            if ( (esp_timer_get_time() - press_time) > FACTORY_RESET_AFTER ) 
            {
                ESP_LOGW(TAG, "Factory reset (not yet)");
            }
        }

        // disable wifi ap after x minutes.
        // only, when device is fully initialized
        if ( device_initialized() && get_wifi_ap_mode() && (esp_timer_get_time() - ap_enabled_time) > DISABLE_AP_AFTER ) 
        {
            ESP_LOGW( TAG, "Disable AP using Timer" );
            update_wifi_ap_mode( false );
        }

        vTaskDelay( interval / portTICK_PERIOD_MS );
    } 

    //destroy_wifi();
}
