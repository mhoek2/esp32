#include "common.h"

#include "esp_spiffs.h"
#include "cJSON.h"
#include <sys/stat.h>

static void init_spiffs( void )
{
    esp_vfs_spiffs_conf_t conf = {
        .base_path              = "/spiffs",
        .partition_label        = "spiffs", // match n partitions.csv
        .max_files              = 5,
        .format_if_mount_failed = true
    };

    esp_err_t ret = esp_vfs_spiffs_register(&conf);
    if (ret != ESP_OK) {
        return;
    }

    size_t total = 0, used = 0;
    esp_spiffs_info("spiffs", &total, &used);
    //ESP_LOGI("spiffs infno", "total: %d, used: %d", total, used);
}

void init_filesystem( void )
{
    init_spiffs();
}