<?php
namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseController;
use CodeIgniter\I18n\Time;

use App\Models\Devices;
use App\Models\DeviceGroups;

class DeviceController extends BaseController
{
	protected $deviceModel;
	protected $deviceGroupsModel;
	
    public function __construct() {
		$this->deviceModel = new Devices();
		$this->deviceGroupsModel = new DeviceGroups();
    }

	public function update_map() 
	{
		$device_id 	= (int)$this->request->getPost('device_id');
		$device = $this->deviceModel->getDevices($device_id);

	   	if ( empty($device)){
	        return $this->response->setJSON([
				'status' 			=> 'suerrorccess',
				'message' 			=> 'No device with this ID',
				'errors'			=> null,
				'redirect'			=> null,
				'new_csrf_token'	=> csrf_hash(),
			]);
		}

		$mac_address = $device[0]['mac'];

        $data = [
            'map_x' => (float)$this->request->getPost('map_x'),
            'map_y' => (float)$this->request->getPost('map_y')
        ];

		$db = \Config\Database::connect();
        $builder = $db->table('devices');
        $builder->where('mac', $mac_address)->update($data);

        return $this->response->setJSON([
			'status' 			=> 'success',
			'message' 			=> 'Device map positional data has been saved',
			'errors'			=> null,
			'redirect'			=> null,
			'new_csrf_token'	=> csrf_hash(),
		]);
	}

	public function update( int $device_id ) 
	{
		$device = $this->deviceModel->getDevices($device_id);
	   
	   	if ( empty($device)){
			die("No device with this id");
		}
		
		$mac_address = $device[0]['mac'];
		
		helper(['form']);
		
        $validation = \Config\Services::validation();
		
        $data = [
            'name' => $this->request->getPost('name'),
            'group_id' => $this->request->getPost('group_id'),
        ];
		
        $rules = [
            'name' => 'required|min_length[2]',
            'group_id' => 'required|min_length[1]',
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
        $builder = $db->table('devices');
        $builder->where('mac', $mac_address)->update($data);
		
		//return redirect()->to(route_to('admin.user', $user_id));
		
        return $this->response->setJSON([
			'status' 			=> 'success',
			'message' 			=> 'Device meta has been saved',
			'errors'			=> null,
			'redirect'			=> null,
			'new_csrf_token'	=> csrf_hash(),
		]);
	}
	
   public function index( int $device_id ): string
    {
		$device = $this->deviceModel->getDevices($device_id);
	   
	   	if ( empty($device)){
			die("No device with this id");
		}
	   
	   	$this->data['device'] = $device[0];
		$this->data['device_groups'] = $this->deviceGroupsModel->findAll();

		load_header( $this->data );
		load_footer( $this->data );
		
        return view('admin/device', $this->data);
    }
}