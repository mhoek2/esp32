<?php
$step = 0;

$steps = [
    "sta_setup.html",
    "server_setup.html",
    "overview.html",
];

// simulate xhr requests locally
// keep syntax as similar to the firmware written in c for clarity
function httpd_resp_set_type( $type )
{
    header('Content-Type: ' . $type );
}

function httpd_resp_send( $response )
{
    die( $response );
}

function xhr_set_wifi_sta( $post ) 
{
    $response = "{\"data\":{\"sta_ssid\":\"%s\", \"sta_passphrase\":\"%s\"}, \"status\":{\"flag\":\"success\", \"message\":\"Wifi AP SSID changed, device will reboot now\"}}";

    httpd_resp_set_type("application/json");
    httpd_resp_send( $response );
}

function xhr_set_server_ip( $post ) 
{
    $response = "{\"data\":{\"server_ip\":\"%s\"}, \"status\":{\"flag\":\"success\", \"message\":\"Set the server IP\"}}";

    httpd_resp_set_type("application/json");
    httpd_resp_send( $response );
}

function xhr_handler( $post )
{
    try {
        // retrieve the operation mode
        if ( empty( $post['mode'] ) )
            throw new Exception('mode keyword is missing in POST data');

        $mode = $post['mode'];

        // operation mode dispatching
        if ( $mode == "set_wifi_ap" )
        {
            xhr_set_wifi_sta( $post );
        }
        else if ( $mode == "set_server_ip" )
        {
            xhr_set_server_ip( $post );
        }
        else 
        {
            throw new Exception('invalid mode');
        }
    }
    catch( Exception $e ) {
        $response = "{\"status\":\"invalid mode!\"}";

        httpd_resp_set_type("application/json");
        httpd_resp_send( $response );
    }
}

// simulate xhr requests
if ( !empty($_POST) ) {
    xhr_handler( $_POST );
}

// most basic way to locally serve a webpage that is stored on the SoC
// NOTE: This does not simulate xhr traffic
if ( array_key_exists( $step, $steps ) && is_file( $steps[$step] ) ) {
    include $steps[$step];
    exit();
}
   
die("Invalid page");
?>