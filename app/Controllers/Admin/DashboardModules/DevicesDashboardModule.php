<?php

namespace App\Controllers\Admin\DashboardModules;

use App\Controllers\Admin\DashboardModules\DashboardModule;
use App\Models\Devices;
use App\Models\Protocol27;

/**
 * Dashboard Module
 *
 * @package App\Controllers\Admin\DashboardModules
 *
 */
class DevicesDashboardModule extends DashboardModule
{
	protected int $sort = 5;
	protected string $css_class = 'wide';
	
    public function __construct() {
		$this->deviceModel = new Devices();
		$this->protocol_27 = new Protocol27();
    }	
	
    public function index( &$data ) : string
   	{
		$this->data = $data;

		$this->deviceModel = new Devices();
		$this->data['devices'] = $this->deviceModel->getDevices();
		
		for ( $i=0; $i < count($this->data['devices']); $i++ )
		{
			$device = &$this->data['devices'][$i];
			
			switch( $device['protocol'] )
			{
				case 27:
					$device['data'] = $this->protocol_27->where([
						'mac' => $device['mac']
					])->find()[0];
					break;
				default:
					break;
			}
		}
		
		
		return view('front/dashboard_modules/devices', $this->data);
	}
}
	 