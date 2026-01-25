<?php
namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseController;
use CodeIgniter\I18n\Time;

use App\Models\Devices;

class DevicesController extends BaseController
{
	protected $deviceModel;
	
    public function __construct() {
		$this->deviceModel = new Devices();
    }

    public function index(): string
    {
		// User
		$this->data['devices'] = $this->deviceModel->findAll();
		
		load_header( $this->data );
		load_footer( $this->data );
		
        return view('admin/devices', $this->data);
    }
}