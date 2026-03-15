<?php

namespace App\Controllers\Front\DashboardModules;

use App\Controllers\Front\DashboardModules\DashboardModule;
use Exception;

/**
 * Dashboard Module
 *
 * @package App\Controllers\Front\DashboardModules
 *
 */
class DevicesDashboardModule extends DashboardModule
{
	protected int $sort = 5;
	protected string $css_class = 'full';
	
    public function __construct() {
		$this->devices = service('device_info');
    }	
	
	private function load_devices_views( &$data, &$devices )
	{
		for ( $i=0; $i < count($devices); $i++ )
		{
			$device = &$devices[$i];
			$data['device'] = &$device;

			try {
				if ( empty($device['data']) )
					throw new Exception("data is missing");

				switch( $device['protocol'] )
				{
					case 27:
						$device['view'] = view('front/dashboard_modules/devices/protocol_27', $data);
						break;
					default:
						throw new Exception("invalid protocol");
						break;
				}
			}
			catch ( Exception $e ) {
				$data['device_exception'] = $e;
				$device['view'] = view('front/dashboard_modules/devices/exception', $data);
			}
		}
	}

    public function index( &$data ) : string
   	{
		$this->data = $data;

		$this->data['devices'] = $this->devices->getDevices();
		$this->devices->load_devices_stats( $this->data['devices'] );

		// load views
		$this->load_devices_views( $this->data, $this->data['devices'] );

		return view('front/dashboard_modules/devices', $this->data);
	}
}
	 