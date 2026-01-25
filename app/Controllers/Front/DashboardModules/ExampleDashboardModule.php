<?php

namespace App\Controllers\Front\DashboardModules;

use App\Controllers\Front\DashboardModules\DashboardModule;

/**
 * Dashboard Module
 *
 * @package App\Controllers\Front\DashboardModules
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

		return view('front/dashboard_modules/example', $this->data);
	}	
}
	 