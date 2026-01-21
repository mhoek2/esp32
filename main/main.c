#include <stdio.h>
#include "freertos/FreeRTOS.h"
#include "freertos/task.h"
#include "driver/gpio.h"
#include "sdkconfig.h"

#include "wifi.h"
#include "webserver.h"
#include "filesystem.h"

#define LED_GPIO 8

static int interval = 500;

static uint8_t s_led_state = 0;

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

void app_main( void )
{
    configure_led();
    init_wifi_ap();
 
    init_filesystem();
    init_webserver();

    while (1) {
        blink_led();

        s_led_state = !s_led_state;
        vTaskDelay( interval / portTICK_PERIOD_MS );
    } 
}
