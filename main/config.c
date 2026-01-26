#include "common.h"
#include "config.h"

#include "esp_spiffs.h"
#include "cJSON.h"
#include <sys/stat.h>

// https://github.com/nopnop2002/esp-idf-json/blob/master/json-basic-object/main/main.c
static config_t config = { 0 };
static const char *false_true[] = {"false", "true"};

config_t *get_config( void )
{
    return &config;
}

// yep, use wrapper functions for those
// to keep it all in one place
void config_set_sta_initialized( void )
{
    config.sta_initialized = true;
}

void config_set_sta_ssid( const char *ssid )
{
    memcpy( &config.sta_ssid, ssid, sizeof(config.sta_ssid) );
}

void config_set_sta_passphrase( const char *passphrase )
{
    memcpy( &config.sta_passphrase, passphrase, sizeof(config.sta_passphrase) );
}

void config_set_server_initialized( void )
{
    config.server_initialized = true;
}

void config_set_server_adress( const char *adress )
{
    memcpy( &config.server_address, adress, sizeof(config.server_address) );
}

esp_err_t set_factory_config( void )
{
    // fill local config sturct
    memset( &config, 0, sizeof(config) );

    config.sta_initialized = false;
    config_set_sta_ssid("ESP32 Wifi");
    config_set_sta_passphrase("12345678");

    config.server_initialized = false;
    config_set_server_adress("127.0.0.1");

    return ESP_OK;
}

esp_err_t write_config( void )
{
    // create json equivilant
    char json_config[256] = { 0 };
    sprintf( json_config, "{\"sta_initialized\": %s, \"sta_ssid\":\"%s\", \"sta_passphrase\":\"%s\", \"server_initialized\":\"%s\", \"server_address\":\"%s\"}", 
        false_true[config.sta_initialized], 
        config.sta_ssid, 
        config.sta_passphrase,
        false_true[config.server_initialized],
        config.server_address
    );
    
    // write to /spiffs/config.json
    FILE *f = fopen("/spiffs/config.json", "w");
    if (f == NULL) {
        return ESP_FAIL;
    }

    fputs(json_config, f);
    fclose(f);

    return ESP_OK;   
}

esp_err_t write_factory_config( void )
{
    set_factory_config();

    return write_config();
}

esp_err_t init_config( void )
{
    struct stat st;
    
    // on boot, file config with factory values,
    // then scan the filesuystem and overwrite with the stored config.
    set_factory_config();

    // check if the file exists and is readable
    // if so, assume there is a valid config
    if ( stat("/spiffs/config.json", &st) != 0 ) {
        return write_factory_config();
    }
    
    FILE *f = fopen( "/spiffs/config.json", "r" );
    if ( f == NULL ){
        return write_factory_config();
    }
        
    fclose( f );
    return ESP_OK;
}

esp_err_t parse_config( cJSON *json_config )
{
    memset( &config, 0, sizeof(config) );

	if ( cJSON_GetObjectItem( json_config, "sta_initialized" ) ) {
		config.sta_initialized = cJSON_GetObjectItem( json_config, "sta_initialized" )->valueint;
    }
    
	if ( cJSON_GetObjectItem( json_config, "sta_ssid" ) ) {
        sprintf( config.sta_ssid, cJSON_GetObjectItem( json_config, "sta_ssid" )->valuestring ); 
    }

  	if ( cJSON_GetObjectItem( json_config, "sta_passphrase" ) ) {
        sprintf( config.sta_passphrase, cJSON_GetObjectItem( json_config, "sta_passphrase" )->valuestring ); 
    }

  	if ( cJSON_GetObjectItem( json_config, "server_initialized" ) ) {
        config.server_initialized = cJSON_GetObjectItem( json_config, "server_initialized" )->valueint;
    }

  	if ( cJSON_GetObjectItem( json_config, "server_address" ) ) {
        sprintf( config.server_address, cJSON_GetObjectItem( json_config, "server_address" )->valuestring ); 
    }

    return ESP_OK;
}

esp_err_t read_config( void )
{
    cJSON *json_config;
    
    FILE *f = fopen( "/spiffs/config.json", "r" );

    // redundant, just to be extra safe
    if ( f == NULL )
    {
        write_factory_config();
        return ESP_FAIL;
    }

    // seek from 0 to end, grab size then point f* to first bit
    fseek( f, 0, SEEK_END );
    long size = ftell( f );
    rewind( f );

    // allocate the json buffer with the size from the file
    char *json_buf = malloc( size + 1 );
    if ( json_buf == NULL ) {
        fclose( f );
        return ESP_ERR_NO_MEM;
    }

    // read file
    fread( json_buf, 1, size, f );
    json_buf[size] = '\0';

    fclose(f);

    // parse
    json_config = cJSON_Parse( json_buf );
    free(json_buf);  

    if ( json_config == NULL ) 
    {
        write_factory_config();
        return ESP_FAIL;
    }

    // set config from JSON
    parse_config( json_config );

    // free
    cJSON_Delete( json_config );

    return ESP_OK;
}

esp_err_t get_settings( void )
{
    
    return ESP_OK;
}
