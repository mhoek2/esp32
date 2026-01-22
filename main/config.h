#ifndef CONFIG_H
#define CONFIG_H

#include "common.h"

typedef struct {
    bool initialized;
    char SSID[32];
} config_t;

config_t *get_config( void );
esp_err_t init_config( void );
esp_err_t read_config( void );


#endif // CONFIG_H