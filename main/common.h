#ifndef COMMON_H
#define COMMON_H

#include <stdio.h>
#include "freertos/FreeRTOS.h"
#include "freertos/task.h"
#include "driver/gpio.h"
#include "sdkconfig.h"
#include <string.h>

#define DV_PROTOCOL 27

#define FACTORY_RESET_AFTER     to_ms( 10 )

// 60 * 5 = 5 minutes
#define DISABLE_AP_AFTER        to_ms( 60 * 5 )

// register device to server after wifi sta connected
// on fail, retry
#define REGISTER_DEVICE_AFTER   to_ms( 5 )

// update window state
#define UPDATE_WINDOWSTATE_AFTER   to_ms( 5 )

uint64_t to_ms( int32_t seconds );

// queue
void queue_reboot( void );

int get_window_state();

#endif // COMMON_H