<?php
namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\Devices;

// Protocols
use App\Models\Protocol27;

class DeviceControler extends BaseController
{
    public function __construct() {
		$this->deviceModel = new Devices();
		$this->protocol_27 = new Protocol27();

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
	
	public function receive()
	{
		// encode the received payload
		$json = file_get_contents('php://input');
		$data = json_decode($json, true);
		$rows = [];
		
		if ( json_last_error() === JSON_ERROR_NONE )
		{
			$valid_data = false;
			$keys = array_keys($data);
			
			// check if received data is valid
			if ( in_array("mac", $keys) && in_array("protocol", $keys) )
			{
				$valid_data = true;

				$rows = $this->deviceModel->where([
					'mac' => $data['mac']
				])->find();
			}
			
			// device is valid
			if ( $valid_data && !empty($rows) )
			{
				// swtich by protocol later..
				// if protocol == 27
				// in_array("state", $keys)
				$state = $data['state'];
					
				$rows = $this->protocol_27->replace([ 
					'mac' 		=> $data['mac'], 
					'state' 	=> $state
					]
				);

				return $this->response->setJSON([
					"recv_state" 	=> $state,
					"valid_data" 	=> $valid_data,
					'status'		=> true,
				]);
				// endif
			}
		}
		
		return $this->response->setJSON([
			'status'	=> false,
		]);
	}
	
    public function register()
    {
		// encode the received payload
		$json = file_get_contents('php://input');
		$data = json_decode($json, true);
		$rows = [];
		
		if ( json_last_error() === JSON_ERROR_NONE )
		{
			$valid_data = false;
			$keys = array_keys($data);
			
			// check if received data is valid
			if ( in_array("mac", $keys) && in_array("protocol", $keys) )
			{
				$valid_data = true;

				$rows = $this->deviceModel->where([
					'mac' => $data['mac']
				])->find();
			}
			
			// if data is valid, and device was not registered yet
			// register the device
			if ( $valid_data && empty($rows) )
			{
				$rows = $this->deviceModel->insert([ 
					'mac' 		=> $data['mac'], 
					'name' 		=> "New Device", 
					'protocol' 	=> $data['protocol']
					]
				);
			}
			
			// return registered state
			return $this->response->setJSON([
				"registered" 	=> !empty($rows),
				"valid_data" 	=> $valid_data,
				'status'		=> true,
			]);
		}
		
		return $this->response->setJSON([
			'status'	=> false,
		]);
    }
}
?>