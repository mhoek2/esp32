#ifndef WEBCLIENT_H
#define WEBCLIENT_H

void init_webclient( void );

bool webclient_is_device_registered( void );
void webclient_register_device( void );
void webclient_update_windowstate( void );
void webclient_set_sta_sleep( void );

#endif // WEBCLIENT_H