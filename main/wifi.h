#ifndef WIFI_H
#define WIFI_H

typedef struct {
    char ssid[64];
} wifi_ap_list_t;

typedef struct {
    int64_t     time_limit;
    int64_t     enabled_time;
    bool        enabled;
    bool        connected;
} wifi_state_t;

typedef enum {
    WIFI_TYPE_STA,
    WIFI_TYPE_AP,
    WIFI_TYPES_MAX,
} wifi_mode_type_t;

void init_wifi( void );
void destroy_wifi( void );

uint16_t        *get_scan_ap_count( void );
wifi_ap_list_t  *get_scan_ap_data( void );

// shared
int64_t get_wifi_enabled_time( wifi_mode_type_t type );
void    reset_wifi_enabled_time( wifi_mode_type_t type );
bool    get_wifi_sta_connected( void );
bool    get_wifi_enabled( wifi_mode_type_t type );
bool    wifi_timer_hit( wifi_mode_type_t type );

// AP
void    wifi_ap_configure( void );
void    update_wifi_ap_mode( bool use_ap );
void    ap_scan_dispatch_async( void );

// STA
void wifi_sta_configure( void );
void update_wifi_sta_mode( bool use_sta );
void queue_wifi_connect( void );
#endif // WIFI_H