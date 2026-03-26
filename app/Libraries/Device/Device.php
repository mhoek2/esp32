<?php
namespace App\Libraries\Device;

use Exception;

use App\Models\Devices;
use App\Models\DeviceEvents;

use App\Libraries\Device\Protocols\DeviceProtocol27;

class Device
{
    protected $raw; // raw data from database
    public $id;
    public $mac;
    public $protocol;

    protected $deviceModel;
    protected $deviceEventsModel;

    protected $protocol_map;

    public function __construct( $mac, $protocol_id = null ) 
    {
        $this->deviceModel = new Devices();
        $this->deviceEventsModel = new DeviceEvents();

		$this->protocol_map = [
			27 => DeviceProtocol27::class,
		];

        // setup instance
        $this->raw = $this->deviceModel->where([
            'mac' => $mac
        ])->first();

        // device not found!
        if ( empty($this->raw) )
            throw new Exception("Invalid device");

        // populate instance data
        $this->id = (int)$this->raw['id'];
        $this->mac = (string)$this->raw['mac'];

        // find the requested protocol
        $this->set_protocol_instance( $protocol_id );

        return $this;
    }

    private function set_protocol_instance( $protocol_id )
    {
        // when multiple protocols are ever implemented per device
        // this $this->protocol should become an array ..

        if ( $protocol_id && isset($this->protocol_map[$protocol_id]) ) {
            $protocol_class = $this->protocol_map[$protocol_id];

            $this->protocol = new $protocol_class( 
                $this
            );

            return;
        }

        $this->protocol = null;
    }

    public function sleep()
    {
        if ( (int)$this->raw['sleep'] === 1 )
            return; // no change

        $this->deviceModel->update( $this->id, [
            'sleep' => 1
        ]);

        $this->add_event( 'receive', ['sleep' => 1] );
    }
    public function awake()
    {
        if ( (int)$this->raw['sleep'] === 0 )
            return; // no change

        $this->deviceModel->update( $this->id, [
            'sleep' => 0
        ]);

        $this->add_event( 'receive', ['sleep' => 0] );
    }

    public function add_event( string $type, array $data ) 
    {
        return $this->deviceEventsModel->add(
            $this->mac,
            $type,
            $data
        );
    }
}
?>