<?php
namespace App\Controllers\DeviceProtocols;

use App\Controllers\DeviceProtocols\DeviceProtocol;
use Exception;

// Protocols
use App\Models\Protocol27;

class DeviceProtocol27 extends DeviceProtocol
{
    protected $protocolModel;
     
    public function __construct( $device ) 
    {
        parent::__construct( $device );

        $this->protocolModel = new Protocol27();
    }

    public function receive( $data )
    {
        if ( !isset($data['state']) )
            throw new Exception("Missing JSON state");

        $state = $data['state'];

        $this->protocolModel->replace([ 
            'mac' 		=> $this->device_mac, 
            'state' 	=> $state
            ]
        );

        // JSON encoded in dispatcher
        return [
            "recv_state" 	=> $state,  // return current state to validate sync
            'status'		=> true,
        ];
    }
}
?>