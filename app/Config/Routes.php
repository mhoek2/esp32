<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

/**
 * front
 */

$routes->get(	'login', 		'LoginController::loginView');
$routes->post(	'login', 		'LoginController::loginAction');

$routes->get(	'/', 					'LoginController::loginView',			['filter' => \App\Filters\AuthFilterGuest::class]);
$routes->get(	'/home',				'Front\Home::application',				['as' => 'home', 'filter' => \App\Filters\AuthFilterSession::class]);
$routes->get(	'download/(:any)',		'DownloadController::index/$1', 		['as' => 'front.download', 'namespace' => 'App\Controllers\Front', 'filter' => \App\Filters\AuthFilterSession::class]);

$routes->get(	'/hw_info',				'HardwareInfo::index',					['as' => 'hw_info', 'filter' => \App\Filters\AuthFilterSession::class]);

$routes->get(	'/get_device_stats',	'DeviceControler::get_stats',			['as' => 'get_device_stats', 'filter' => \App\Filters\AuthFilterSession::class]);

$routes->post(	'/register_device',		'DeviceControler::register',			['as' => 'register_device']);
$routes->post(	'/receive_device',		'DeviceControler::receive',				['as' => 'receive_device']);
$routes->post(	'/set_sta_sleep',		'DeviceControler::set_sta_sleep',		['as' => 'set_sta_sleep']);

/**
 * admin
 */
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => \App\Filters\AuthFilterAdmin::class], function ($routes) 
{
	$routes->get(	'',								'Home::dashboard', 									['as' => 'admin']);
	
	// Devices	   
	$routes->get(	'devices',						'DevicesController::index', 						['as' => 'admin.devices']);
				   
	$routes->group('devices/(:num)', function ($routes)
	{
		$routes->get(	'', 						'DeviceController::index/$1', 						['as' => 'admin.device']);
		$routes->post(	'', 						'DeviceController::update/$1', 						['as' => 'admin.device.update']);
	});
	$routes->post(	'device/update_map', 			'DeviceController::update_map', 					['as' => 'admin.device.update_map']);

	
	// User
	$routes->get(	'users/new', 					'UserController::new_user', 						['as' => 'admin.user.new']);
	$routes->post(	'users/new', 					'UserController::new_user_create');
	$routes->get(	'users',						'UsersController::index', 							['as' => 'admin.users']);
	
	$routes->group('users/(:num)', function ($routes)
	{
		$routes->get(	'', 						'UserController::index/$1', 						['as' => 'admin.user']);
		$routes->post(	'', 						'UserController::update/$1', 						['as' => 'admin.user.update']);
		$routes->post(	'delete', 					'UserController::delete/$1', 						['as' => 'admin.user.delete']);
		$routes->post(	'change_password', 			'UserController::change_password/$1', 				['as' => 'admin.user.change_password']);
	});
	
	// Files
	$routes->group('files/', function ($routes)
	{
		$routes->get(	'', 						'FilesController::index', 							['as' => 'admin.files']);
		$routes->post(	'upload', 					'FilesController::upload', 							['as' => 'admin.files_upload']);
		$routes->post(	'delete_file', 				'FilesController::delete_file', 					['as' => 'admin.files_delete']);
	});
});

service('auth')->routes($routes);