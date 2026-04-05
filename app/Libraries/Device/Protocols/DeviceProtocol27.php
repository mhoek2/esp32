<?php
namespace App\Libraries\Device\Protocols;

use App\Libraries\Device\Device;
use App\Libraries\Device\Protocols\DeviceProtocol;
use Exception;

// Protocols
use App\Models\Protocol27;

class DeviceProtocol27 extends DeviceProtocol
{
    protected $protocolModel;
     
    public function __construct( Device $device ) 
    {
        parent::__construct( $device );

        $this->protocolModel = new Protocol27();
    }

    public function receive( array $data ) : array
    {
        if ( !isset($data['state']) )
            throw new Exception("Missing JSON state");

        $state = (int)$data['state'];

        $this->protocolModel->replace([ 
            'mac' 		=> $this->device->mac, 
            'state' 	=> $state
            ]
        );

        $this->device->add_event( 'receive', ['state' => $state] );

        // JSON encoded in dispatcher
        return [
            "recv_state" 	=> $state,  // return current state to validate sync
            'status'		=> true,
        ];
    }
}
?>