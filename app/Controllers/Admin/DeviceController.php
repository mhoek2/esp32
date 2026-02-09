<?php
namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseController;
use CodeIgniter\I18n\Time;

use App\Models\Devices;

class DeviceController extends BaseController
{
	protected $deviceModel;
	
    public function __construct() {
		$this->deviceModel = new Devices();
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
        ];
		
        $rules = [
            'name' => 'required|min_length[2]',
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

		load_header( $this->data );
		load_footer( $this->data );
		
        return view('admin/device', $this->data);
    }
}