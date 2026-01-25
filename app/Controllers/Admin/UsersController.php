<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseController;
use CodeIgniter\I18n\Time;

use App\Models\Users;

class UsersController extends BaseController
{
	protected $userModel;
	
    public function __construct() {
		$this->userModel = new Users();
    }

    public function index(): string
    {
		// User
		$this->data['users'] = $this->userModel->getUsers();
		
		load_header( $this->data );
		load_footer( $this->data );
		
        return view('admin/users', $this->data);
    }
}