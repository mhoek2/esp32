<?php
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

class wifi_state_t {
    public $time_limit; 
    public $enabled_time; 
    public $enabled; 

    public function __construct($_time_limit, $_enabled_time, $_enabled) {
        $this->time_limit   = $_time_limit;
        $this->enabled_time = $_enabled_time;
        $this->enabled      = $_enabled;
    }
}

class Wifi {
    public const WIFI_TYPE_STA = 0;
    public const WIFI_TYPE_AP  = 1;
    public const WIFI_TYPES_MAX = 2;

    private array $wifi_state = [];

    public function __construct( $state ) 
    {
        $this->wifi_state = [
            self::WIFI_TYPE_STA => new wifi_state_t(
                0,      // time_limit
                0,      // enabled_time
                $state[self::WIFI_TYPE_STA],   // enabled
            ),
            self::WIFI_TYPE_AP => new wifi_state_t(
                0,      // time_limit
                0,      // enabled_time
                $state[self::WIFI_TYPE_AP],   // enabled
            )
        ];
    }

    public function get_wifi_states()
    {
        return $this->wifi_state;
    }

    public function get_wifi_enabled( int $type )
    {
        if ( $type >= self::WIFI_TYPES_MAX )
            return false;

        return $this->wifi_state[$type]->enabled;
    }
}

class PHPSim 
{
    //
    // config
    //
    private $step = 1;
    private $steps = [
        "sta_setup.html",
        "server_setup.html",
        "overview.html",
    ];
    private $wifi_sta_enabled = true;
    private $wifi_ap_enabled = false;
    //
    // config end
    //

    private Wifi $wifi;
    private $wifi_state;

    private $uri_handlers = [];

    public function __construct() 
    {
        $this->define_endpoints();

        $this->wifi = new Wifi(
            [
                Wifi::WIFI_TYPE_STA => $this->wifi_sta_enabled,
                Wifi::WIFI_TYPE_AP  => $this->wifi_ap_enabled
            ]
        );
        //var_dump($this->wifi->get_wifi_enabled($this->wifi::WIFI_TYPE_STA));
    }

    public function root_get_handler() 
    {
        // most basic way to locally serve a webpage that is stored on the SoC
        // NOTE: This does not simulate xhr traffic
        if ( array_key_exists( $this->step, $this->steps ) && is_file( $this->steps[$this->step] ) ) {
            include $this->steps[$this->step];
            exit();
        }
    }

    // xhr requests
    private function httpd_resp_set_type( $type ) 
    {
        header('Content-Type: ' . $type );
    }

    private function httpd_resp_send( $response ) 
    {
        die( $response );
    }

    private function xhr_invalid_mode()
    {
        $response = "{\"status\":\"invalid mode!\"}";

        $this->httpd_resp_set_type("application/json");
        $this->httpd_resp_send( $response );
    }

    private function xhr_set_wifi_sta( $post ) 
    {
        $response = "{\"data\":{\"sta_ssid\":\"%s\", \"sta_passphrase\":\"%s\"}, \"status\":{\"flag\":\"success\", \"message\":\"Wifi AP SSID changed, device will reboot now\"}}";

        $this->httpd_resp_set_type("application/json");
        $this->httpd_resp_send( $response );
    }

    private function xhr_set_server_ip( $post ) 
    {
        $response = "{\"data\":{\"server_ip\":\"%s\"}, \"status\":{\"flag\":\"success\", \"message\":\"Set the server IP\"}}";

        $this->httpd_resp_set_type("application/json");
        $this->httpd_resp_send( $response );
    }

    public function xhr_handler() 
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
                $this->xhr_set_wifi_sta( $post );
            }
            else if ( $mode == "set_server_ip" ) 
            {
                $this->xhr_set_server_ip( $post );
            }
            else 
            {
                throw new Exception('invalid mode');
            }
        }
        catch( Exception $e ) {
            $this->xhr_invalid_mode();
        }
    }

    public function status_handler()
    {
        $false_true = ["false", "true"];
        $response = sprintf("{\"wifi_sta_connected\": %s}",
            $false_true[$this->wifi->get_wifi_enabled($this->wifi::WIFI_TYPE_STA)]
        );

        $this->httpd_resp_set_type("application/json");
        $this->httpd_resp_send( $response );
    }

    public function get_factory_reset_handler() 
    {
        $response = "{\"data\":{}, \"status\":{\"flag\":\"success\", \"message\":\"Device is reset\"}}";

        $this->httpd_resp_set_type("application/json");
        $this->httpd_resp_send( $response );
    }

    public function reboot_device_handler() 
    {
        $response = "{\"data\":{}, \"status\":{\"flag\":\"success\", \"message\":\"Device is rebooting\"}}";

        $this->httpd_resp_set_type("application/json");
        $this->httpd_resp_send( $response );
    }

    public function disable_ap_handler() 
    {
        $response = "{\"data\":{}, \"status\":{\"flag\":\"success\", \"message\":\"AP is disabling\"}}";

        $this->httpd_resp_set_type("application/json");
        $this->httpd_resp_send( $response );
    }

    private function ADD_URI_HANDLER( $_method, $_uri, $_handler ) 
    {
        $this->uri_handlers[] = new httpd_uri_t(
            $_uri,
            $_method,
            [$this, $_handler]
        );
    }

    private function define_endpoints() 
    {
        $this->ADD_URI_HANDLER( "_GET", "/",               "root_get_handler" );
        $this->ADD_URI_HANDLER( "_GET", "/xhr",            "xhr_handler" );
        $this->ADD_URI_HANDLER( "_GET", "/factory_reset",  "get_factory_reset_handler" );
        $this->ADD_URI_HANDLER( "_GET", "/reboot_device",  "reboot_device_handler" );
        $this->ADD_URI_HANDLER( "_GET", "/disable_ap",     "disable_ap_handler" );
        $this->ADD_URI_HANDLER( "_GET", "/status",         "status_handler" );
    }

    public function handle_request( $request_uri ) 
    {
        foreach ( $this->uri_handlers as $handler ) {
            if ( $handler->uri === $request_uri ) {
                $handler->run();
            }
        }

        die("Invalid page");
    }
}

$sim = new PHPSim();

if ( !empty($_ENV) ) 
    $sim->handle_request( $_ENV['REQUEST_URI'] );
?>