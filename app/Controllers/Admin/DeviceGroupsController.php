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

    public function delete()
    {
        $group_id = $this->request->getPost('group_id');

        if (empty($group_id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid group ID!','new_csrf_token' => csrf_hash()]);
        }
      
        $members =  $this->deviceGroupModel->hasMembers($group_id);

        if ($members)
            return $this->response->setJSON(['status' => 'error', 'message' => 'Group has members!', 'new_csrf_token' => csrf_hash()]);

        $this->deviceGroupModel->delete($group_id);

        return $this->response->setJSON(['status' => 'success', 'new_csrf_token' => csrf_hash()]);
    }

    public function index(): string
    {
        $this->data['device_groups'] = $this->deviceGroupModel->getDeviceGroups();

        load_header( $this->data );
        load_footer( $this->data );
		
        return view('admin/device_groups', $this->data);
    }
}