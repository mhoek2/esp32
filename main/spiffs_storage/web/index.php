<?php
// step/page
$step = 1;
$steps = [
    "sta_setup.html",
    "server_setup.html",
    "overview.html",
];

class httpd_uri_t {
    public $uri; 
    protected $method; 
    protected $handler; 

    public function __construct($_uri, $_method, $_handler) {
        $this->uri      = $_uri;
        $this->method   = $_method;
        $this->handler  = $_handler;
    }

    public function run() {
        call_user_func( $this->handler );
    }
}

$uri_handlers = [];

function root_get_handler()
{
    global $step;
    global $steps;

    // most basic way to locally serve a webpage that is stored on the SoC
    // NOTE: This does not simulate xhr traffic
    if ( array_key_exists( $step, $steps ) && is_file( $steps[$step] ) ) {
        include $steps[$step];
        exit();
    }
}

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

function &xhr_handler()
{
    // retrieve the operation mode
    if ( empty( $_POST['mode'] ) )
        throw new Exception('mode keyword is missing in POST data');

    $post = &$_POST;

    try {
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

function &get_factory_reset_handler ()
{
    $response = "{\"data\":{}, \"status\":{\"flag\":\"success\", \"message\":\"Device is reset\"}}";

    httpd_resp_set_type("application/json");
    httpd_resp_send( $response );
}

function &reboot_device_handler()
{
    $response = "{\"data\":{}, \"status\":{\"flag\":\"success\", \"message\":\"Device is rebooting\"}}";

    httpd_resp_set_type("application/json");
    httpd_resp_send( $response );
}

function &disable_ap_handler()
{
    $response = "{\"data\":{}, \"status\":{\"flag\":\"success\", \"message\":\"AP is disabling\"}}";

    httpd_resp_set_type("application/json");
    httpd_resp_send( $response );
}

function ADD_URI_HANDLER( $_method, $_uri, $_handler, &$count )
{
    global $uri_handlers;

    $uri_handlers[$count++] = new httpd_uri_t(
        $_uri,
        $_method,
        $_handler
    );
}

function define_endpoints()
{
    $num_uri_handlers = 0;

    ADD_URI_HANDLER( "_GET", "/",               "root_get_handler",             $num_uri_handlers );
    ADD_URI_HANDLER( "_GET", "/xhr",            "xhr_handler",                  $num_uri_handlers );
    ADD_URI_HANDLER( "_GET", "/factory_reset",  "get_factory_reset_handler",    $num_uri_handlers );
    ADD_URI_HANDLER( "_GET", "/reboot_device",  "reboot_device_handler",        $num_uri_handlers );
    ADD_URI_HANDLER( "_GET", "/disable_ap",     "disable_ap_handler",           $num_uri_handlers );
}

define_endpoints();

// handle endpoints
if ( !empty($_ENV) ) {
    foreach ( $uri_handlers as $handler )
    {
        if ( $handler->uri === $_ENV['REQUEST_URI'] )
        {
            $handler->run();
        }
    }
}

die("Invalid page");
?>