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

#define URL_FORMAT "http://%s/%s"

static const char *TAG = "webclient";
static char mac_address[18];

// register device
static bool device_registered = false;

// registered requests
typedef enum {
    REQUEST_REGISTER,
    REQUEST_UPDATE_WINDOWSTATE,
    NUM_REQUESTS
} request_id_t;

typedef struct {
    const char*     name;
    request_id_t    request_id;
    esp_timer_cb_t  call;
    char            url[128];
    char            payload[128];
    esp_err_t       (*on_response)(esp_http_client_event_t *e);
    void            (*do_retry)(void);
    uint64_t        delay;
    esp_timer_handle_t timer_handle;
} http_request_ctx_t;
static http_request_ctx_t http_request[NUM_REQUESTS];

// global
static void set_mac_address( void )
{
    uint8_t mac[6];

    esp_read_mac( mac, ESP_MAC_WIFI_STA );
 
    snprintf( mac_address, sizeof(mac_address), "%02X:%02X:%02X:%02X:%02X:%02X",
             mac[0], mac[1], mac[2], mac[3], mac[4], mac[5] );
}

static esp_err_t http_event_handler( esp_http_client_event_t *event )
{
    if ( event->event_id != HTTP_EVENT_ON_DATA )
        return ESP_OK;

    http_request_ctx_t *ctx = (http_request_ctx_t *)event->user_data;

    if ( ctx && ctx->on_response )
        return ctx->on_response( event );

    return ESP_OK;
}

static esp_err_t http_send_post( http_request_ctx_t *ctx )
{
    esp_http_client_config_t http_cfg = {
        .url            = ctx->url,
        .method         = HTTP_METHOD_POST,
        .event_handler  = http_event_handler,
        .user_data      = (void*)ctx,
    };

    ESP_LOGW( TAG, "POST %s", ctx->url );
    ESP_LOGW( TAG, "Payload: %s", ctx->payload );

    esp_http_client_handle_t client = esp_http_client_init(&http_cfg);

    esp_http_client_set_header(client, "Content-Type", "application/json");
    esp_http_client_set_post_field(client, ctx->payload, strlen(ctx->payload));

    esp_err_t err = esp_http_client_perform(client);
    if ( err == ESP_OK ) {
        ESP_LOGI(TAG, "HTTP POST Status = %d, content_length = %lld",
                 esp_http_client_get_status_code(client),
                 esp_http_client_get_content_length(client));
    } 

    else {
        ESP_LOGE(TAG, "HTTP POST request failed: %s", esp_err_to_name(err));

        // retry if ptr is set
        if ( ctx && ctx->do_retry )
        {
            ESP_LOGE( TAG, "Retry .." );
            ctx->do_retry();
        }
    }

    esp_http_client_cleanup(client);

    return err;
}

static esp_err_t http_request_dispatch( int request_id )
{
    http_request_ctx_t *ctx = &http_request[request_id];

    if ( !ctx || !ctx->timer_handle )
    {
        ESP_LOGE( TAG, "ctx or timer is invalid for request id [%d]!", request_id );
        return ESP_FAIL;
    }

    esp_timer_start_once( ctx->timer_handle, ctx->delay );

    return ESP_OK;
}

// register device
bool webclient_is_device_registered( void )
{
    return device_registered;
}

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
    ESP_LOGW( TAG, "is_registered: %d", (int)webclient_is_device_registered() );

    if ( !webclient_is_device_registered() )
    {
        // retry
        webclient_register_device();
    }

    return ESP_OK;
}

static void register_device( void *arg )
{
    config_t *config = get_config();
    http_request_ctx_t *ctx = &http_request[REQUEST_REGISTER];

    snprintf( ctx->url, sizeof(ctx->url), URL_FORMAT, config->server_address, "register_device" );
    snprintf( ctx->payload, sizeof(ctx->payload), "{\"mac\":\"%s\",\"protocol\":%d}", 
        mac_address, 
        DV_PROTOCOL 
    );
    http_send_post( ctx );
}

void webclient_register_device( void )
{
    if ( !device_initialized() )
        return;

    http_request_dispatch( REQUEST_REGISTER );
}

// window state
static esp_err_t update_window_response( esp_http_client_event_t *event )
{
    cJSON *json_config;

    json_config = cJSON_Parse( (char *)event->data );

    if ( json_config == NULL ) 
    {
        ESP_LOGI(TAG, "invalid JSON config");
        return ESP_FAIL;
    }

    int recv_state = -1;

    ESP_LOGW( TAG, "Update response: %.*s", event->data_len, (char *)event->data );
	if ( cJSON_GetObjectItem( json_config, "recv_state" ) ) {
		recv_state = cJSON_GetObjectItem( json_config, "recv_state" )->valueint;
    }

    // check if recv state matches current state, 
    // else dispatch new request
    if ( recv_state != get_window_state() )
    {
        webclient_update_windowstate();
        ESP_LOGW( TAG, "Sent/Recveived state did not match current state, RETRY!" );
    }

    return ESP_OK;
}

static void update_window_state( void *arg )
{
    config_t *config = get_config();
    http_request_ctx_t *ctx = &http_request[REQUEST_UPDATE_WINDOWSTATE];
    
    int state = get_window_state();
    if ( state < 0 )
    {
        ESP_LOGE( TAG, "Sensor reads -1, this is invalid! Sensor fault. RETRY!" );
        webclient_update_windowstate();
    }
    
    snprintf( ctx->url, sizeof(ctx->url), URL_FORMAT, config->server_address, "receive_device" );
    snprintf( ctx->payload, sizeof(ctx->payload), "{\"mac\":\"%s\",\"protocol\":%d, \"state\":%d}", 
        mac_address, 
        DV_PROTOCOL,
        state
    );
    http_send_post( ctx );   
}

void webclient_update_windowstate( void )
{
    if ( !device_initialized() )
        return;

    http_request_dispatch( REQUEST_UPDATE_WINDOWSTATE );
}

// init
static void init_http_requests( void )
{
    uint16_t i;

    http_request[REQUEST_REGISTER] = (http_request_ctx_t){
        .name           = "webclient_register_device",
        .request_id     = REQUEST_REGISTER,
        .call           = &register_device,
        .on_response    = register_device_response,
        .do_retry       = webclient_register_device,
        .delay          = REGISTER_DEVICE_AFTER
    };

    http_request[REQUEST_UPDATE_WINDOWSTATE] = (http_request_ctx_t){
        .name           = "webclient_update_windowstate",
        .request_id     = REQUEST_UPDATE_WINDOWSTATE,
        .call           = &update_window_state, 
        .on_response    = update_window_response,
        .do_retry       = webclient_update_windowstate,
        .delay          = UPDATE_WINDOWSTATE_AFTER
    };

    // create timers
    for ( i = 0; i < NUM_REQUESTS; i++ )
    {
        http_request_ctx_t *ctx = &http_request[i];

        const esp_timer_create_args_t args = {
            .callback   = ctx->call,
            .name       = ctx->name
        };

        ESP_ERROR_CHECK( esp_timer_create( &args, &ctx->timer_handle ) ); 
    }
}

// global
void init_webclient( void )
{
    set_mac_address();

    init_http_requests();
}