<?php

namespace App\Models;

use CodeIgniter\Model;

class DeviceEvents extends Model
{
    protected $table      = 'device_events';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'mac', 'type', 'json', 'created_at'];	

    protected $validationRules = [
        'type' => 'required|in_list[sta_sleep,sta_awake,receive]',
    ];

    public function getByMac( string $mac )
    {
        return $this->where('mac', $mac)->findAll();
    }

    public function add( string $mac, string $type, array $data ) 
    {
        $insert = [
            'mac'  => $mac,
            'type' => $type,
            'json' => json_encode($data),
        ];

        if ( !$this->validate( $insert ) )
            return false;

        return $this->insert( $insert );
    }
}
