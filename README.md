# ESP32 Prototype

Prototyping Firmware for ESP32 SoCs along with a separate self-hosted web-based dashboard.

The ESP32 Firmware ships with a basic setup page accessable through a browser by connecting to the WiFi AP (Hotspot), Allowing to enter the WiFi SSID and Passphrase of your WLAN. Followed by the IP of the management dashboard.

A protocol identifier index is used to distinguish/separate sensor data. \
As an example protocol ```27``` uses a ```magnetic contact switch```.
Allowing to track when a physical window is in a open or closed state.

Connected devices are listed in the management dashboard, displaying the ```Activity state``` and ```Protocol status```

| Dashboard | Firmware |
|-------|----------------|
| ![](https://mhoek2.github.io/assets/illustrations/esp32.png) | ![](https://mhoek2.github.io/assets/illustrations/esp32-firmware.png) |