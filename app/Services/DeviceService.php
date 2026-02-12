<?php
namespace App\Services;

use App\Models\Devices;
use App\Models\Protocol27;

/**
 * Provides service to store user meta
 *
 * @example
 * ```
 * $meta = service('user_meta');
 * $meta->save( 'key', 'string/json' );
 * $value = $meta->find( 'key' );
 * ```
 *
 * @package App\Services
 *
 */
class DeviceService
{
	/**
	 * This method initializes the `User` object, retrieves the user's information, and ensures the user is valid. 
	 * If the user is not valid, the process is halted with an error message. 
	 *
	 * It also initializes the appropriate `user_meta`
	 *
	 * @throws Exception If the user data is invalid or missing, the action will be halted with an error message.
	 */
	public function __construct( )
	{
		$this->deviceModel = new Devices();
		$this->protocol_27 = new Protocol27();
	}


	public function load_devices_stats( &$devices )
	{
		for ( $i=0; $i < count($devices); $i++ )
		{
			$device = &$devices[$i];
			
			switch( $device['protocol'] )
			{
				case 27:
					$device['data'] = $this->protocol_27->where([
						'mac' => $device['mac']
					])->find()[0];
					break;
				default:
					break;
			}
		}
	}

	public function getDevices( ): array
	{
		return $this->deviceModel->getDevices();
	}
}