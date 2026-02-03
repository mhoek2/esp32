file(GLOB SRCS "webclient.c" "wifi.c" "config.c" "filesystem.c" "main.c" "webserver.c" "*.c")
idf_component_register(
    SRCS ${SRCS} 
    INCLUDE_DIRS "."
)

spiffs_create_partition_image(spiffs spiffs_storage FLASH_IN_PROJECT)
