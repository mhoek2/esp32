<?php
namespace App\Controllers\DeviceProtocols;

use CodeIgniter\Controller;

use App\Models\Devices;
use App\Models\DeviceEvents;

class DeviceProtocol extends Controller
{
    protected $device_id;
    protected $device_mac;

    protected $deviceModel;
    protected $deviceEventsModel;

    public function __construct( $device ) 
    {
        $this->device_id = (int)$device['id'];
        $this->device_mac = (string)$device['mac'];

        $this->deviceModel = new Devices();
        $this->deviceEventsModel = new DeviceEvents();
    }	

    public function sleep()
    {
        $this->deviceModel->update( $this->device_id, [
            'sleep' => 1
        ]);
    }
    public function awake()
    {
        $this->deviceModel->update( $this->device_id, [
            'sleep' => 0
        ]);
    }

    public function add_event( string $type, array $data ) 
    {
        if ( !$this->deviceEventsModel->validate_event_type( $type ) )
            return false;

        $this->deviceEventsModel->add(
            $this->device_mac,
            $type,
            $data
        );
    }
}
?>