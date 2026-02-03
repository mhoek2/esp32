#ifndef COMMON_H
#define COMMON_H

#include <stdio.h>
#include "freertos/FreeRTOS.h"
#include "freertos/task.h"
#include "driver/gpio.h"
#include "sdkconfig.h"
#include <string.h>

#define DV_PROTOCOL 27

// queue
void queue_reboot( void );

#endif // COMMON_H