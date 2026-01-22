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
void config_set_initialized( void )
{
    config.initialized = true;
}

void config_set_ap_ssid( const char *ssid )
{
    memcpy( &config.ap_ssid, ssid, sizeof(config.ap_ssid) );
}

void config_set_ap_passphrase( const char *passphrase )
{
    memcpy( &config.ap_passphrase, passphrase, sizeof(config.ap_passphrase) );
}

esp_err_t set_factory_config( void )
{
    // fill local config sturct
    memset( &config, 0, sizeof(config) );

    config.initialized = false;
    config_set_ap_ssid("SuperWIFI");
    config_set_ap_passphrase("12345678");

    return ESP_OK;
}

esp_err_t write_config( void )
{
    // create json equivilant
    char json_config[256] = { 0 };
    sprintf( json_config, "{\"initialized\": %s, \"ap_ssid\":\"%s\", \"ap_passphrase\":\"%s\"}", 
        false_true[config.initialized], 
        config.ap_ssid, 
        config.ap_passphrase 
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

	if ( cJSON_GetObjectItem( json_config, "initialized" ) ) {
		config.initialized = cJSON_GetObjectItem( json_config, "initialized" )->valueint;
    }
    
	if ( cJSON_GetObjectItem( json_config, "ap_ssid" ) ) {
		char *ap_ssid = cJSON_GetObjectItem( json_config, "ap_ssid" )->valuestring;
        sprintf( config.ap_ssid, ap_ssid ); 
    }

  	if ( cJSON_GetObjectItem( json_config, "ap_passphrase" ) ) {
		char *ap_passphrase = cJSON_GetObjectItem( json_config, "ap_passphrase" )->valuestring;
        sprintf( config.ap_passphrase, ap_passphrase ); 
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
