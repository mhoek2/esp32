<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseController;

use App\Models\Meetings;

class Home extends BaseController
{
    public function dashboard(): string
    {
		load_header( $this->data );
		load_footer( $this->data );
		
        return view('admin/dashboard', $this->data);
    }
}
