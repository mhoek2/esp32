<?php
namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseController;
use CodeIgniter\I18n\Time;

use App\Models\DeviceGroups;

class DeviceGroupsController extends BaseController
{
	protected $deviceGroupModel;
	
    public function __construct() {
		$this->deviceGroupModel = new DeviceGroups();
    }

    public function index(): string
    {
		$this->data['device_groups'] = $this->deviceGroupModel->findAll();
		
		load_header( $this->data );
		load_footer( $this->data );
		
        return view('admin/device_groups', $this->data);
    }
}