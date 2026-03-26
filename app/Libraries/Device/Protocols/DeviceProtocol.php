<?php
namespace App\Libraries\Device\Protocols;

use App\Models\Devices;

use App\Libraries\Device\Device;

abstract class DeviceProtocol
{
    protected Device $device;

    protected $deviceModel;

    public function __construct( Device $device ) 
    {
        $this->device = $device;

        $this->deviceModel = new Devices();
    }

    abstract public function receive( array $data ) : array;
}
?>