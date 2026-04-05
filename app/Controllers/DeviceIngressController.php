<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use Exception;

use App\Models\Devices;

use App\Libraries\Device\Device;

class DeviceIngressController extends BaseController
{
    protected $deviceModel;
    protected $devices;

    public function __construct() {
		$this->deviceModel = new Devices();

		$this->devices = service('device_info');
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

        $device = new Device( $data['mac'], (int)$data['protocol'] );

        if ( !$device->protocol )
            throw new Exception("Invalid protocol");

        return [
            'data'          => $data,
            'device'        => $device,
            'protocol_id'   => (int)$data['protocol']
        ];
    }

	public function set_sta_sleep()
	{
        try {
            $input  = $this->validate_device_input();
            $device = &$input['device'];
            $data   = &$input['data'];
            // $protocol_id   = $input['protocol_id'];

            $sleep = (int)$data['state'];

            if ( $sleep === 0 )
                $device->awake(); 
            else
                $device->sleep();
            
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
            $input  = $this->validate_device_input();
            $device = &$input['device'];
            $data   = &$input['data'];
            // $protocol_id   = $input['protocol_id'];

            // awake
            $device->awake();

            // dispatch to protocol
            $dispatch = $device->protocol->receive( $data );
            
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