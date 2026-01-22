#ifndef CONFIG_H
#define CONFIG_H

#include "common.h"

typedef struct {
    bool initialized;
    char ap_ssid[32];
    char ap_passphrase[32];
} config_t;

config_t *get_config( void );
esp_err_t init_config( void );
esp_err_t read_config( void );
esp_err_t write_config( void );

void config_set_initialized( void );
void config_set_ap_ssid( const char *ssid );
void config_set_ap_passphrase( const char *passphrase );

#endif // CONFIG_H