#include "common.h"
#include "config.h"
#include "wifi.h"
#include "webserver.h"
#include "filesystem.h"

#include <esp_log.h>

#define LED_GPIO 8

static const char *TAG = "main";
static int interval = 1500;

static uint8_t s_led_state = 0;

// simple queue
static bool _queue_reboot = false;

void queue_reboot( void )
{
    _queue_reboot = true;
}

void set_interval( int speed )
{
    //interval = speed;
}

static void blink_led( void )
{
    gpio_set_level(LED_GPIO, s_led_state);
}
static void configure_led( void )
{
    gpio_reset_pin(LED_GPIO);
    gpio_set_direction(LED_GPIO, GPIO_MODE_OUTPUT);
}

void app_main( void )
{
    //configure_led();
    
 
    // enable AP by default for now
    //update_wifi_mode( true );

    init_filesystem();

    init_config();
    
    if ( read_config() < 0 )
    {
        read_config();  // retry once, as fail-safe
    }

    init_wifi();
    init_webserver();

    while (1) {
        //blink_led();
        ESP_LOGI(TAG, "blink state");

        s_led_state = !s_led_state;
        vTaskDelay( interval / portTICK_PERIOD_MS );

        // queued restart
        //if ( _queue_reboot ){
            //esp_restart();
        //}
    } 

    //destroy_wifi();
}
