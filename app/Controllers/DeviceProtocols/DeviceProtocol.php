<?php
namespace App\Controllers\DeviceProtocols;

use CodeIgniter\Controller;

use App\Models\Devices;

class DeviceProtocol extends Controller
{
    protected $device_id;
    protected $device_mac;

    protected $deviceModel;

    public function __construct( $device ) 
    {
        $this->device_id = (int)$device['id'];
        $this->device_mac = (string)$device['mac'];

        $this->deviceModel = new Devices();
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
}
?>