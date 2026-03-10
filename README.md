# ESP32 - Firmware

This branch contains the Firmware.
SoC used for prototyping: ```ESP32 Super Mini C3```

Developed using VSCode, not Arduino IDE. \
In VSCode ESP-IDF (Espressif) Extension is used for serial connection with IDF_TARGET: esp32c3

Usfull terminal commands: \
Open termninal: ```CTRL``` + ```Shift``` + ```P``` → Type: ESP-IDF: Open ESP-IDF Terminal.


```bash
  # build the firmare locally
  idf.py build

  # flash to SoC
  idf.py flash

  # monitor the serial output
  idf.py monitor

  # remove/reset IDF_TARGET
  Remove-Item Env:IDF_TARGET
  setx IDF_TARGET esp32c3

  # open menuconfig
  start idf.py menuconfig

  # edit manual partitions
  gen_esp32part.py partitions.csv build/partition_table/partition-table.bin
```
