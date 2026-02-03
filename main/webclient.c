#include "common.h"
#include "config.h"
#include "wifi.h"
#include "webclient.h"

#include <string.h>
#include <esp_system.h>
#include <esp_http_client.h>
#include <esp_log.h>
#include "esp_mac.h"
#include "esp_timer.h"

#include "cJSON.h"

#define URL_FORMAT "http://%s/windowstate/public/%s"

static const char *TAG = "webclient";
static char mac_str[18];

// register device
static esp_timer_handle_t register_device_timer;
static bool device_registered = false;

typedef enum {
    REQUEST_REGISTER = 1,
} request_id_t;

typedef struct {
    request_id_t    request_id;
    esp_err_t       (*on_response)(esp_http_client_event_t *e);
    void            (*retry)(void);
} http_request_ctx_t;

static esp_err_t http_event_handler( esp_http_client_event_t *event )
{
    if ( event->event_id != HTTP_EVENT_ON_DATA )
        return ESP_OK;

    http_request_ctx_t *ctx = (http_request_ctx_t *)event->user_data;

    if ( ctx && ctx->on_response )
        return ctx->on_response( event );

    return ESP_OK;
}

static esp_err_t http_send_post( const char *url, const char *payload, http_request_ctx_t *ctx )
{
    esp_http_client_config_t http_cfg = {
        .url = url,
        .method = HTTP_METHOD_POST,
        .event_handler = http_event_handler,
        .user_data = (void*)ctx,
    };

    ESP_LOGW( TAG, "POST %s", url );
    ESP_LOGW( TAG, "Payload: %s", payload );

    esp_http_client_handle_t client = esp_http_client_init(&http_cfg);

    esp_http_client_set_header(client, "Content-Type", "application/json");
    esp_http_client_set_post_field(client, payload, strlen(payload));

    esp_err_t err = esp_http_client_perform(client);
    if (err == ESP_OK) {
        ESP_LOGI(TAG, "HTTP POST Status = %d, content_length = %lld",
                 esp_http_client_get_status_code(client),
                 esp_http_client_get_content_length(client));
    } 
    else {
        ESP_LOGE(TAG, "HTTP POST request failed: %s", esp_err_to_name(err));

        if ( ctx && ctx->retry )
        {
            ESP_LOGE( TAG, "Retry .." );
            ctx->retry();
        }
    }

    esp_http_client_cleanup(client);

    return err;
}

// register device
static esp_err_t register_device_response( esp_http_client_event_t *event )
{
    cJSON *json_config;

    json_config = cJSON_Parse( (char *)event->data );

    if ( json_config == NULL ) 
    {
        ESP_LOGI(TAG, "invalid JSON config");
        return ESP_FAIL;
    }

	if ( cJSON_GetObjectItem( json_config, "registered" ) ) {
		device_registered = cJSON_GetObjectItem( json_config, "registered" )->valueint;
    }

    ESP_LOGW( TAG, "Registered: %.*s", event->data_len, (char *)event->data );
    ESP_LOGW( TAG, "is_registered: %d", (int)device_registered );

    if ( !device_registered )
    {
        // retry
        webclient_register_device();
    }

    return ESP_OK;
}

static void register_device( void *arg )
{
    config_t *config = get_config();

    char url[256];
    char payload[128];

    snprintf( url, sizeof(url), URL_FORMAT, config->server_address, "register_device" );
    snprintf( payload, sizeof(payload), "{\"mac\":\"%s\",\"protocol\":%d}", mac_str, DV_PROTOCOL );
   
    static http_request_ctx_t ctx = {
        .request_id     = REQUEST_REGISTER,
        .on_response    = register_device_response,
        .retry          = webclient_register_device
    };
 
    http_send_post( url, payload, &ctx );
}

void webclient_register_device( void )
{
    esp_timer_start_once( register_device_timer, 5000 * 1000 );
}

static void init_register_device_timer( void )
{
    const esp_timer_create_args_t args = {
        .callback   = &register_device,
        .name       = "webclient_register_device"
    };

    ESP_ERROR_CHECK( esp_timer_create( &args, &register_device_timer ) );
}

void validate_server( void )
{
    // not implemented
}

void init_webclient( void )
{
    uint8_t mac[6];

    esp_read_mac( mac, ESP_MAC_WIFI_STA );
    snprintf( mac_str, sizeof(mac_str),
             "%02X:%02X:%02X:%02X:%02X:%02X",
             mac[0], mac[1], mac[2], mac[3], mac[4], mac[5] );


    init_register_device_timer();
}