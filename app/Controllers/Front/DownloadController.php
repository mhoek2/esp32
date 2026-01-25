<?php

namespace App\Controllers\Front;

use App\Controllers\Front\BaseController;

use App\Models\Uploads;

class DownloadController extends BaseController
{

    public function index( $any )
    {
		static $uploads = new Uploads();
		$permission = false;
		
		//$segments = service('uri')->getSegments();
		$segments = $this->request->getUri()->getSegments();

		array_shift($segments);
		$relative_path = urldecode(implode('/', $segments));
		$absolute_path = WRITEPATH . $relative_path;

		$filedata = $uploads->where('path', $relative_path)->find();

		if ( !is_array($filedata) || empty($filedata) )
		{
			throw new \CodeIgniter\Exceptions\PageNotFoundException("Not found");
		}

		$filedata = $filedata[0];
		
		if ( is_null($filedata) || !file_exists($absolute_path) ) {
			throw new \CodeIgniter\Exceptions\PageNotFoundException("Not found");
		}
		
		// global file, only need user session ( user session check is redundant )
		if ( (int)$filedata['global'] === 1 && $this->data['user'] ){
			$permission = true;
		}

		// own file
		if ( $filedata['user_id'] === $this->data['user']['id'] ) {
			$permission = true;
		}

		// not own file, but admin can open it
		else if ( $this->data['user']['is_admin'] ) {
			$permission = true;
		}
		
		
		if ( !$permission ) {
			return response()
				->setStatusCode(403)
				->setBody('You do not have permission.')
				->send();
		}
		
		return $this->response->download($absolute_path, null);
    }
}
