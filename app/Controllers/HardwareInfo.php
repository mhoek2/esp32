<?php
namespace App\Controllers;

use App\Controllers\BaseController;

class HardwareInfo extends BaseController
{
    public function index()
    {
		$hwinfo = service('hardware_info');
		
		
		// test for XAMPP on windows
		$testData = [
			'os_version' => "Linux rpi4 6.8.0-1044-raspi #48-Ubuntu SMP PREEMPT_DYNAMIC Tue Nov 25 15:21:15 UTC 2025 aarch64 aarch64 aarch64 GNU/Linux\n",
			'hw_version' => "Raspberry Pi 4 Model B Rev 1.1\0",
			'cpu_temp' => 47.225,
			'cpu_load' => [
				0,
				0,
				1,
				0,
			],
			'memory' => [
				'total' => '3.7 GB',
				'free'  => '2.88 GB',
				'used'  => '833.88 MB',
				'used_pct' => 22.03,
			],
			'cpu_cores' => 4,
		];

		return $this->response->setJSON([
			//'data' 				=> $testData, 
			'data' 				=> $hwinfo->getAll(), 
			'new_csrf_token' 	=> csrf_hash(),
		]);
    }
}
?>