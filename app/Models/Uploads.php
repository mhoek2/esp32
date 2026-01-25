<?php

namespace App\Models;

use CodeIgniter\Model;

class Uploads extends Model
{
    protected $table      = 'uploads';
    protected $primaryKey = 'id';

    protected $allowedFields = ['id', 'user_id', 'global', 'path', 'filename', 'extension', 'mime_type', 'bytes', 'created_at'];
	
}