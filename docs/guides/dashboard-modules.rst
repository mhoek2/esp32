Dashboard Modules
=================

Quick overview on how modular dashboard is set up.

Create a new module
-------------------

#. Create a php file in app/Controllers/Front/DashboardModules
#. use the 'DashbordModule' suffix.
#. Make sure the class and filename are the same

The dashboard_helper handles the rest

Example module
--------------
.. code-block:: php
    namespace App\Controllers\Front\DashboardModules;
    
    use App\Controllers\Front\DashboardModules\DashboardModule;
    
    class ExampleDashboardModule extends DashboardModule
    {
    	protected $sort = 5;
    	protected $css_class = 'wide';
    	//protected $visible = false;
    
    	public function index( &$data ) : string
    	{
    		// example usage of user meta:
    		$user_meta = service('user_meta');
    		$this->data['meta_data'] = $user_meta->find( 'key' );
			
    		return view('front/dashboard_modules/example_view', $this->data);	
    	}
    }	