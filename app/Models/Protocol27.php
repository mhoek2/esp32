<?php

namespace App\Models;

use CodeIgniter\Model;

class Protocol27 extends Model
{
    protected $table      = 'protocol_27';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'mac', 'state'];	
}