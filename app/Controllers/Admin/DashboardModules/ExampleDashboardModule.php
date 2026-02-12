<?php

namespace App\Controllers\Admin\DashboardModules;

use App\Controllers\Admin\DashboardModules\DashboardModule;

/**
 * Dashboard Module
 *
 * @package App\Controllers\Admin\DashboardModules
 *
 */
class ExampleDashboardModule extends DashboardModule
{
	protected int $sort = 5;
	protected string $css_class = 'wide';

    public function index( &$data ) : string
    {
		$this->data = $data;
		
		//$hwinfo = service('hardware_info');
		//$this->data["hw_info"] = $hwinfo->getAll();

		return view('admin/dashboard_modules/example', $this->data);
	}	
}
	 