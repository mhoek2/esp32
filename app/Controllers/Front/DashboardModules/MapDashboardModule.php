<?php

namespace App\Controllers\Front\DashboardModules;

use App\Controllers\Front\DashboardModules\DashboardModule;
use App\Models\DeviceGroups;

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

	protected $devices;
	protected $deviceGroupsModel;

    public function __construct() {
		$this->device = service('device_info');
		$this->deviceGroupsModel = new DeviceGroups();
    }
	
    public function index( &$data ) : string
   	{
		$this->data = $data;

		$this->data['devices'] = $this->devices->getDevices();		
		$this->data['device_groups'] = $this->deviceGroupsModel->findAll();

		return view('front/dashboard_modules/map', $this->data);
	}
}
	 