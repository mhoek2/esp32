<?php

namespace App\Controllers\Front\DashboardModules;

use App\Controllers\Front\DashboardModules\DashboardModule;
use App\Models\Devices;

/**
 * Dashboard Module
 *
 * @package App\Controllers\Front\DashboardModules
 *
 */
class DevicesDashboardModule extends DashboardModule
{
	protected int $sort = 5;
	protected string $css_class = 'wide';
	
    public function index( &$data ) : string
    {
		$this->data = $data;

		$this->deviceModel = new Devices();
		$this->data['devices'] = $this->deviceModel->findAll();
		
		return view('front/dashboard_modules/devices', $this->data);
	}
}
	 