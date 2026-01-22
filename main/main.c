#include "common.h"
#include "config.h"
#include "wifi.h"
#include "webserver.h"
#include "filesystem.h"

#include "driver/temperature_sensor.h"

#define LED_GPIO 8

static int interval = 500;

static uint8_t s_led_state = 0;

// simple queue
static bool _queue_reboot = false;

void queue_reboot( void )
{
    _queue_reboot = true;
}

void set_interval( int speed )
{
    interval = speed;
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

static temperature_sensor_handle_t temp_handle = NULL;

float get_temp( void )
{
    float tsens_out;
    ESP_ERROR_CHECK(temperature_sensor_get_celsius( temp_handle, &tsens_out ));

    return tsens_out;
}

void app_main( void )
{
    
    temperature_sensor_config_t temp_sensor_config = TEMPERATURE_SENSOR_CONFIG_DEFAULT(20, 50);
    ESP_ERROR_CHECK(temperature_sensor_install(&temp_sensor_config, &temp_handle));
    ESP_ERROR_CHECK(temperature_sensor_enable(temp_handle));

    configure_led();
    init_wifi_ap();
 
    init_filesystem();

    init_config();
    
    if ( read_config() < 0 )
    {
        // retry once, as fail-safe
        read_config();
    }

    init_webserver();

    while (1) {
        blink_led();

        s_led_state = !s_led_state;
        vTaskDelay( interval / portTICK_PERIOD_MS );

        // queued restart
        if ( _queue_reboot ){
            //esp_restart();
        }
    } 
}
