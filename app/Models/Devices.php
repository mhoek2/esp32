<?php

namespace App\Models;

use CodeIgniter\Model;

class Devices extends Model
{
    protected $table      = 'devices';
    protected $primaryKey = 'id';
    protected $allowedFields = ['mac', 'protocol'];	
}