<?php
namespace App\Controllers;

use App\Controllers\BaseController;

class HardwareInfo extends BaseController
{
    public function index()
    {
		$hwinfo = service('hardware_info');
		
		return $this->response->setJSON([
			'data' 				=> $hwinfo->getAll(), 
			'new_csrf_token' 	=> csrf_hash(),
		]);
    }
}
?>