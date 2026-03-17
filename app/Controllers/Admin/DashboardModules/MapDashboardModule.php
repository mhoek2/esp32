<?php

namespace App\Controllers\Admin\DashboardModules;

use App\Controllers\Admin\DashboardModules\DashboardModule;
use App\Models\DeviceGroups;

/**
 * Dashboard Module
 *
 * @package App\Controllers\Admin\DashboardModules
 *
 */
class MapDashboardModule extends DashboardModule
{
	protected int $sort = 5;
	protected string $css_class = 'full';

	protected $devices;
	protected $deviceGroupsModel;

    public function __construct() {
		$this->devices = service('device_info');
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
	 