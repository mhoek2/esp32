<?php

namespace App\Controllers\Front\DashboardModules;

use App\Controllers\Front\DashboardModules\DashboardModule;

/**
 * Dashboard Module
 *
 * @package App\Controllers\Front\DashboardModules
 *
 */
class MapDashboardModule extends DashboardModule
{
	protected int $sort = 5;
	protected string $css_class = 'full';
	
    public function __construct() {
		$this->devices = service('device_info');
    }
	
    public function index( &$data ) : string
   	{
		$this->data = $data;

		$this->data['devices'] = $this->devices->getDevices();		

		return view('front/dashboard_modules/map', $this->data);
	}
}
	 