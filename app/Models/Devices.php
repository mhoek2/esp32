<?php

namespace App\Models;

use CodeIgniter\Model;

class Devices extends Model
{
    protected $table      = 'devices';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'group_id', 'name', 'mac', 'protocol', 'sleep', 'map_x', 'map_y'];	
	
    public function getDevices( $id_or_mac = NULL )
    {
		$builder = $this->select('devices.*, COALESCE(device_groups.name, "None") as group_name')
					->join('device_meta', 'device_meta.mac = devices.mac', 'left')
					->join('device_groups', 'device_groups.id = devices.group_id', 'left');
					
		if ( !empty($id_or_mac) ) 
		{
			if ( is_int($id_or_mac) )
				$builder->where('devices.id', $id_or_mac);
			
			elseif ( is_string($id_or_mac) )
				$builder->where('devices.mac', $id_or_mac);
		}
	
		$items = $builder->findAll();

		return $items;
    }		
}