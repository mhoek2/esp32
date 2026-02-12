<?php

namespace App\Controllers\Admin\DashboardModules;

use App\Controllers\Admin\DashboardModules\DashboardModule;

/**
 * Dashboard Module
 *
 * @package App\Controllers\Admin\DashboardModules
 *
 */
class DevicesDashboardModule extends DashboardModule
{
	protected int $sort = 5;
	protected string $css_class = 'large';
	
    public function __construct() {
		$this->devices = service('device_info');
    }	
	
    public function index( &$data ) : string
   	{
		$this->data = $data;

		$this->data['devices'] = $this->devices->getDevices();		
		$this->devices->load_devices_stats( $this->data['devices'] );
		
		return view('admin/dashboard_modules/devices', $this->data);
	}
}
	 