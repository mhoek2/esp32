<?php

namespace App\Models;

use CodeIgniter\Model;

class UserMeta extends Model
{
    protected $table      = 'user_meta';
    protected $primaryKey = 'id';

    protected $allowedFields = ['id', 'user_id', 'key', 'value', 'created_at'];
}

