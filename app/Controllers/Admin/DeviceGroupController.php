<?php
namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseController;
use CodeIgniter\I18n\Time;

use App\Models\DeviceGroups;

class DeviceGroupController extends BaseController
{
	protected $deviceGroupModel;
	
    public function __construct() {
		$this->deviceGroupModel = new DeviceGroups();
    }

	public function update( int $device_group_id ) 
	{
		$device_group = $this->deviceGroupModel->find($device_group_id);
	   
	   	if ( empty($device_group)){
			die("No group with this id");
		}
		
		helper(['form']);
		
        $validation = \Config\Services::validation();
		
        $data = [
            'name' => $this->request->getPost('name'),
            'color' => $this->request->getPost('color'),
        ];
		
        $rules = [
            'name' => 'required|min_length[2]',
            'color' => 'required|min_length[3]|max_length[7]',
        ];

		// Validation failed
		if (! $this->validateData($this->request->getPost(), $rules, [], config('Auth')->DBGroup)) {	
			//return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
			
			return $this->response->setJSON([
				'status' 			=> 'error',
				'message' 			=> 'Er is iets mis gegaan',
				'errors'			=> $this->validator->getErrors(),
				'redirect'			=> null,
				'new_csrf_token'	=> csrf_hash(),
			]);
		} 
		
		$db = \Config\Database::connect();
        $builder = $db->table('device_groups');
        $builder->where('id', $device_group_id)->update($data);
		
		//return redirect()->to(route_to('admin.user', $user_id));
		
        return $this->response->setJSON([
			'status' 			=> 'success',
			'message' 			=> 'Device group has been saved',
			'errors'			=> null,
			'redirect'			=> null,
			'new_csrf_token'	=> csrf_hash(),
		]);
	}
	
   public function index( int $device_group_id ): string
    {
		$group = $this->deviceGroupModel->findall($device_group_id);
	   
	   	if ( empty($group)){
			die("No device group with this id");
		}
	   
	   	$this->data['device_group'] = $group[0];

		load_header( $this->data );
		load_footer( $this->data );
		
        return view('admin/device_group', $this->data);
    }
}