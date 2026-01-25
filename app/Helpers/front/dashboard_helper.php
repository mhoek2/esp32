<?php

if (! function_exists('get_dashboard_modules')) {
	
	function getSubclassesOf($parent) {
		$result = array();
		foreach (get_declared_classes() as $class) {
			if (is_subclass_of($class, $parent))
				$result[] = $class;
		}
		return $result;
	}
	
	function get_dashboard_modules( &$data )
	{
		$modules = [];
		
		$namespace = "App\\Controllers\\Front\\DashboardModules\\";
		$dir = APPPATH . 'Controllers/Front/DashboardModules';
		$postfix = 'DashboardModule.php';
		
		foreach (glob($dir . '/*'. $postfix) as $file) {
			$controller_name = basename($file, '.php');
			$controller_class = $namespace . $controller_name;
			
			if (basename($file) === $postfix)
				continue;
			
			if (class_exists($controller_class) && is_subclass_of($controller_class, $namespace. 'DashboardModule')) 
			{
				$controller = new $controller_class();
				$has_index = method_exists($controller, 'index');
				
				$modules[] = [
					'view' 		=> $has_index ? $controller->index($data) : sprintf("index not found for: %s", $controller_class),
					'sort' 		=> $controller->getSort(),
					'css_class' => $controller->getCssClass(),
					'visible' 	=> $controller->getVisibility()
				];
			}
		}
		
		usort($modules, function($a, $b) {
			return $a['sort'] <=> $b['sort']; // Compare based on the 'sort' value
		});
		
		return $modules;
	}
}