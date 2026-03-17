<?php

namespace App\Models;

use CodeIgniter\Model;

class DeviceGroups extends Model
{
    protected $table      = 'device_groups';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'name', 'color'];	
}