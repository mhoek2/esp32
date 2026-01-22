#ifndef COMMON_H
#define COMMON_H

#include <stdio.h>
#include "freertos/FreeRTOS.h"
#include "freertos/task.h"
#include "driver/gpio.h"
#include "sdkconfig.h"
#include <string.h>

// common
void set_interval( int speed );

float get_temp( void );

#endif // COMMON_H