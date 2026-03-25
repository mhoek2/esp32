<?php

namespace App\Models;

use CodeIgniter\Model;

class DeviceEvents extends Model
{
    protected $table      = 'device_events';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'mac', 'type', 'json', 'created_at'];	

    // better way to do this?
    protected $event_types = [
        'sta_sleep', 
        'sta_awake', 
        'receive'
    ];

    function validate_event_type( string $type )
    {
        if ( !in_array( $type, (array) $this->event_types, true )) 
            return false;

        return $type;
    }

    function add( string $mac, string $type, array $data ) 
    {
        $this->db->table($this->table)->insert([
            'mac'           => $mac,
            'type'          => $type,
            'json'          => json_encode( $data ),
        ]);
    }
}
