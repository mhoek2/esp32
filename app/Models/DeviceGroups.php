<?php

namespace App\Models;

use CodeIgniter\Model;

class DeviceGroups extends Model
{
    protected $table      = 'device_groups';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'name', 'color'];	

    public function hasMembers( $group_id ) : bool
    {
        return $this->db->table('devices')->where('group_id', $group_id)->countAllResults() > 0;
    }

    public function getDeviceGroups( $group_id = NULL )
    {
		$builder = $this->select('device_groups.*, COUNT(devices.id) as count')
                    ->join('devices', 'devices.group_id = device_groups.id', 'left')
					->groupBy('device_groups.id');

		$items = $builder->findAll();

		return $items;
    }
}