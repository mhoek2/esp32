#ifndef CONFIG_H
#define CONFIG_H

#include "common.h"

typedef struct {
    bool sta_initialized;
    char sta_ssid[32];
    char sta_passphrase[32];
    bool server_initialized;
    char server_address[32];
} config_t;

config_t *get_config( void );
esp_err_t init_config( void );
esp_err_t read_config( void );
esp_err_t write_config( void );

esp_err_t set_factory_config( void );

void config_set_sta_initialized( void );
void config_set_sta_ssid( const char *ssid );
void config_set_sta_passphrase( const char *passphrase );

void config_set_server_initialized( void );
void config_set_server_adress( const char *adress );

#endif // CONFIG_H