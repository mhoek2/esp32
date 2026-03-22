<?php
namespace App\Services;

use App\Models\Devices;
use App\Models\DeviceGroups;

/**
 * Provides service to set up the floorplan
 *
 *
 * @package App\Services
 *
 */
class FloorplanService
{
	protected $devicesModel;
	protected $deviceGroupsModel;

	protected string $map_image = "/assets/map/floorplan.png";
	protected string $devices;
	protected string $device_groups;

	public function __construct( )
	{
		$this->devicesModel = service('device_info');
		$this->deviceGroupsModel = new DeviceGroups();

		$this->devices = json_encode($this->devicesModel->getDevices());		
		$this->device_groups = json_encode($this->deviceGroupsModel->findAll());
	}

	public function load_style()
	{
		$assets = [
			'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
			base_url('assets/floorplan/floorplan.css'),
		];

		return implode("\n", array_map(function ($href) {
			return '<link rel="stylesheet" href="' . $href . '">';
		}, $assets));
	}
	
	public function load_script()
	{
		$assets = [
			'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
			base_url('assets/floorplan/floorplan.js'),
		];

		return implode("\n", array_map(function ($href) {
			return '<script src="' . $href . '"></script>';
		}, $assets));
	}

	public function getFooterConfigJS( $is_editable )
	{
		$device_update_map_url = $is_editable ? base_url(route_to('admin.device.update_map')) : '';
		
		return [
			'enabled' => true,
			'config' => [
				'id'					=> 'map',
				'floorplan'				=> $this->map_image,
				'bounds'				=> '[[0,0], [9933,12992]]',
				'devices'				=> $this->devices,
				'device_groups'			=> $this->device_groups,
				'device_update_map_url'	=> $device_update_map_url,
				'is_editable'			=> $is_editable
			]
		];
	}
}