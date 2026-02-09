<?php

namespace App\Models;

use CodeIgniter\Model;

class DeviceMeta extends Model
{
    protected $table      = 'device_meta';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'mac'];	
}