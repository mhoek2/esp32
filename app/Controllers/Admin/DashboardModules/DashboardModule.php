<?php

namespace App\Controllers\Admin\DashboardModules;

/**
 * Provides base class for modular dashboard modules
 *
 * To add a module:
 * 1. Create a file in app/Controllers/Admin/DashboardModules
 * 2. use 'DashbordModule' suffix for the filename
 * 3. Make sure the class and filename are equal
 * 4. <em>The dashboard_helper handles the rest<em>
 *
 * @example
 * ```
 * namespace App\Controllers\Admin\DashboardModules;
 * 
 * use App\Controllers\Admin\DashboardModules\DashboardModule;
 * 
 * class ExampleDashboardModule extends DashboardModule
 * {
 *		protected $sort = 5;
 *		protected $css_class = 'wide';
 *		//protected $visible = fakse;
 
 *		public function index( &$data ) : string
 *		{
 *			// example usage of user meta:
 *			$user_meta = service('user_meta');
 *			$this->data['meta_data'] = $user_meta->find( 'key' );<br>
 *			return view('admin/dashboard_modules/example_view', $this->data);	
 *		}
 * }
 * ```
 *
 * @package App\Controllers\Admin\DashboardModules
 *
 */
class DashboardModule
{
	/** Used for the view */
	protected $data = [];
	
	/** Sort order */
	protected int $sort = 100;

	/** Custom css class */
	protected string $css_class = '';
	
	/** Visiblity state, default is true */
	protected bool $visible = true;
	
	/** Returns sort order */
	function getSort() : int 
	{
		return $this->sort;
	}
	
	/** Returns custom css class */
	function getCssClass() : string 
	{
		return $this->css_class;
	}
	
	/** Returns true if module is visible, false if not */
	function getVisibility() : bool
	{
		return $this->visible;
	}	
}
	 