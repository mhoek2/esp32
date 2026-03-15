<?php

namespace App\Models;

use CodeIgniter\Model;

class Devices extends Model
{
    protected $table      = 'devices';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'name', 'mac', 'protocol', 'sleep', 'map_x', 'map_y'];	
	
    public function getDevices( $id_or_mac = NULL )
    {
		$builder = $this->select('devices.*')
					->join('device_meta', 'device_meta.mac = devices.mac', 'left');
					
		if ( !empty($id_or_mac) ) 
		{
			if ( is_int($id_or_mac) )
				$builder->where('devices.id', $id_or_mac);
			
			elseif ( is_string(id_or_mac) )
				$builder->where('devices.mac', $mac);
		}
	
		$items = $builder->findAll();

		return $items;
    }		
}