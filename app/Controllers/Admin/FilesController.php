<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseController;

use CodeIgniter\Files\File;

use App\Models\Uploads;

class FilesController extends BaseController
{
	protected $uploads;
	
    /*public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                    \CodeIgniter\HTTP\ResponseInterface $response,
                                    \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        helper('form');
    }*/
	
    public function __construct() {
		helper('form');
		
		$this->uploads = new Uploads();
    }
	
	private function load_uploaded_files()
	{
		$this->data['uploads'] = $this->uploads->where(['global' => 1])->findAll();
	}
	
	private function load_common_data()
	{
		load_header( $this->data );
		load_footer( $this->data );
		
		$this->data['errors'] = [];
		$this->load_uploaded_files();
	}

    public function index(): string
    {
		$this->load_common_data();
		
        return view('admin/files', $this->data);
    }
	
	public function delete_file(){
		$file_id = $this->request->getPost('file_id');

		if ( empty($file_id) ) 
		{
            return $this->response->setJSON(['status' => 'error', 'new_csrf_token'=> csrf_hash()]);
        }

		$file_info = $this->uploads->find($file_id);

		if ( is_null($file_info) ) 
		{
			return $this->response->setJSON(['status' => 'error', 'new_csrf_token'=> csrf_hash()]);
		}

		$filepath = WRITEPATH . $file_info['path'];

		if ( file_exists( $filepath ) && unlink( $filepath ) ) 
		{
			$this->uploads->delete($file_id);
		}

		return $this->response->setJSON(['status' => 'success', 'new_csrf_token'=> csrf_hash()]);
	}
	
	/**
	 * verifies or creates the requested upload direcory
	 *
	 * @return mixed returns the upload path as string, false boolean on fail.
	 */
	private function verify_upload_directory( string $upload_directory )
	{
		$upload_path = WRITEPATH;

		$directories = explode('/', $upload_directory );
		foreach( $directories as $directory )
		{
			if ( empty($directory) )
				continue;
			
			$upload_path .= sprintf('%s/', $directory);
			
			if ( !is_dir( $upload_path ) )
				mkdir( $upload_path, 0755, true );
		}
		
		return is_dir( $upload_path ) ? $upload_path : false;
	}
	
    public function upload()
    {
        $validationRule = [
            'userfile' => [
                'label' => 'Image File',
                'rules' => [
                    'uploaded[userfile]',
					'mime_in[userfile,image/jpg,image/jpeg,image/gif,image/png,image/webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/plain]',
					'max_size[userfile,10240]',
                ],
            ],
        ];
		
		$this->load_common_data();
		
        if ( !$this->validateData([], $validationRule ) ) 
		{
            $this->data['errors'] = $this->validator->getErrors();
            return redirect()->back()->with('errors', $this->data['errors'] );
        }

		$file = $this->request->getFile('userfile');

        if ( !$file->hasMoved() ) 
		{
			$sub_directory = 'uploads/user_data/';
			$directory = $this->verify_upload_directory( $sub_directory );
			
			// verify that the upload directory is valid!
			if ( !$directory )
			{
				$this->data['errors'][0] = "Upload map is ongeldig";
				return redirect()->back()->with('errors', $this->data['errors'] );
			}
			
			$file->move( $directory, $file->getClientName() );
			
			$filepath = $directory . $file->getClientName();
			$file_info = new File($filepath);
			
			$this->uploads->insert([
				'user_id' 		=> $this->data['user']['id'], 
				'global'		=> 1,
				'path' 			=> $sub_directory . $file->getName(), 
				'filename' 		=> $file->getName(), 
				'extension' 	=> $file->getClientExtension(), 
				'mime_type' 	=> $file->getClientMimeType(), 
				'bytes' 		=> $file->getSizeByUnit(), 
			]);

			$insert_id = $this->uploads->insertID();
			
			// deprecated since redirect back
			//$this->load_uploaded_files(); // re-load
			
			return redirect()->back()->with('success', sprintf("Bestand is geupload") );
        }
	}
	
}