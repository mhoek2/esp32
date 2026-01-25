<?php

namespace App\Controllers\Front;

use App\Controllers\Front\BaseController;

use CodeIgniter\Files\File;

use App\Models\Uploads;

class FilesController extends BaseController
{
	protected $uploads;

    public function __construct() {
		helper('form');
		
		$this->uploads = new Uploads();
    }

	private function load_uploaded_files()
	{
		$sub_directory = sprintf( '%d/%s_meeting_id_%d/', 
						 $this->data['user']['id'], 
						 $this->data['meeting']['name'], 
						 $this->data['meeting']['id'] 
						);

		$this->data['uploads'] = $this->uploads->where([
			'global' 	=> 0,
			'user_id'	=> (int)$this->data['user']['id'],
		])->like('path', $sub_directory)->findAll();
	}
	
	private function load_common_data( int $meeting_id )
	{
		// Meeting
        $this->data['meeting'] = $this->get_meeting( $meeting_id );
        $this->data["current_meeting"] = $this->data["meeting"] != false ? $meeting_id : false;

        // Assignment
        $this->data['assignments'] = $this->assignments->where('meeting_id', $meeting_id)->orderBy('sort_order', 'ASC')->findAll();
		
		// previous and next urls
		$this->data['prev_url'] = base_url(route_to('front.meeting', $meeting_id));
		
		load_header( $this->data );
		load_footer( $this->data );
		load_sidebar( $this->data );
		
		$this->data['errors'] = [];

		$this->load_uploaded_files();
	}
	
    public function index( int $meeting_id ): string
    {
		$this->load_common_data( $meeting_id );
		
        return view('front/files', $this->data);
    }
	
	public function delete_file(){
		$file_id = $this->request->getPost('file_id');

		if ( empty($file_id) ) 
		{
            return $this->response->setJSON(['status' => 'error', 'new_csrf_token'=> csrf_hash()]);
        }

		// !!! CHECK PERMISSIONS/OWNER OF THIS FILE !!!
		$file_info = $this->uploads->where('user_id', (int)$this->data['user']['id'])->find( $file_id );
		
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
	
    public function upload( int $meeting_id )
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
		
		$this->load_common_data( $meeting_id );
		
		if ( is_null($this->data['meeting']) )
		{
            $this->data['errors'][0] = "Invalid meeting!";
			return redirect()->back()->with('errors', $this->data['errors'] );
		}
		
        if ( !$this->validateData([], $validationRule ) ) 
		{
            $this->data['errors'] = $this->validator->getErrors();
			return redirect()->back()->with('errors', $this->data['errors'] );
        }
		
		$file = $this->request->getFile('userfile');

        if ( !$file->hasMoved() ) 
		{
			$sub_directory = sprintf( 'uploads/user_data/%d/%s_meeting_id_%d/', 
									 $this->data['user']['id'], 
									 $this->data['meeting']['name'], 
									 $this->data['meeting']['id'] 
									);
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
				'global'		=> 0,
				'path' 			=> $sub_directory . $file->getName(), 
				'filename' 		=> $file->getName(), 
				'extension' 	=> $file->getClientExtension(), 
				'mime_type' 	=> $file->getClientMimeType(), 
				'bytes' 		=> $file->getSizeByUnit(), 
			]);

			$insert_id = $this->uploads->insertID();

			// deprecated since redirect back
			//$this->load_uploaded_files(); // re-load to get added file

			return redirect()->back()->with('success', sprintf("Bestand is geupload") );
		}
	}
}
