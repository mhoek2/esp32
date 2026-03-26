<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use Exception;

use App\Models\Devices;

// Protocol Controllers
use App\Controllers\DeviceProtocols\DeviceProtocol27;

class DeviceIngressController extends BaseController
{
    protected $deviceModel;
    protected $devices;

	protected $protocol_map;

    public function __construct() {
		$this->deviceModel = new Devices();

		$this->devices = service('device_info');

		$this->protocol_map = [
			27 => DeviceProtocol27::class,
		];
    }	
	
	public function get_stats()
	{
		$devices = $this->devices->getDevices();
		$this->devices->load_devices_stats( $devices );

		return $this->response->setJSON([
			'status'	=> true,
			'devices'	=> $devices
		]);
	}

    private function validate_device_input()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $device = null;

        if ( json_last_error() !== JSON_ERROR_NONE )
            throw new Exception("Invalid JSON");

        $keys = array_keys($data);
        if ( !in_array("mac", $keys) || !in_array("protocol", $keys) )
            throw new Exception("Missing JSON data");

        $device = $this->deviceModel->where([
            'mac' => $data['mac']
        ])->first();

        if ( empty($device) )
            throw new Exception("Invalid device");

        $device_id = (int)$device['id'];
        $protocol_id = (int)$data['protocol'];
        //$protocol_id = (int)$device['protocol'];

        if ( !isset($this->protocol_map[$protocol_id]) )
            throw new Exception("Invalid protocol");

        $protocol_class = $this->protocol_map[$protocol_id];

        $protocol = new $protocol_class( 
            $device
        );

        return [
            'data'      => $data,
            'device'    => $device,
            'protocol'  => $protocol,
        ];
    }

	public function set_sta_sleep()
	{
        try {
            $input = $this->validate_device_input();

            $protocol = $input['protocol'];
            $data     = $input['data'];
            $device   = $input['device'];

            $sleep = (int)$data['state'];

            if ( $sleep !== (int)$device['sleep'])
            {
                if ( $sleep === 0 )
                    $protocol->awake(); 
                else
                    $protocol->sleep();
            }
            
            return $this->response->setJSON([
                "recv_state" 	=> $sleep,
                'status'		=> true,
            ]);
        }
        catch ( Exception $e ) {
            return $this->response->setJSON([
                'status'	=> false,
                'error'     => $e->getMessage()
            ]);
        }
	}
	
	public function receive()
	{
        try {
            $input = $this->validate_device_input();

            $protocol = $input['protocol'];
            $data     = $input['data'];

            // awake
            $protocol->awake();

            // dispatch to protocol
            $dispatch = $protocol->receive( $data );
            
            return $this->response->setJSON(
                 $dispatch
            );
        }
        catch ( Exception $e ) {
            return $this->response->setJSON([
                'status'	=> false,
                'error'     => $e->getMessage()
            ]);
        }
	}
	
    public function register()
    {
        try {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $device = null;

            if ( json_last_error() !== JSON_ERROR_NONE )
                throw new Exception("Invalid JSON");

            $keys = array_keys($data);
            if ( !in_array("mac", $keys) || !in_array("protocol", $keys) )
                throw new Exception("Missing JSON data");
            
            $device = $this->deviceModel->where([
                'mac' => $data['mac']
            ])->first();

            // register only if device does not exist
            if ( !$device )
            {
                $device = $this->deviceModel->insert([ 
                    'mac' 		=> $data['mac'], 
                    'name' 		=> "New Device", 
                    'protocol' 	=> $data['protocol']
                    ]
                );
            }

			return $this->response->setJSON([
				"registered" 	=> !empty($device),
				'status'		=> true,
			]);
        }
        catch ( Exception $e ) {
            return $this->response->setJSON([
                'status'	=> false,
                'error'     => $e->getMessage()
            ]);
        }
    }
}
?>