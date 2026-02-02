#ifndef WIFI_H
#define WIFI_H

typedef struct {
    char ssid[64];
} wifi_ap_list_t;

uint16_t *get_scan_ap_count( void );
wifi_ap_list_t *get_scan_ap_data( void );


void init_wifi( void );
void destroy_wifi( void );

void wifi_sta_configure( void );

void wifi_ap_configure( void );
void wifi_ap_enable( void );
void wifi_ap_disable( void );

void ap_scan_dispatch_async( void );

bool get_wifi_ap_mode( void );
void update_wifi_ap_mode( bool use_ap );

#endif // WIFI_H