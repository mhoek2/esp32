<?php
// app/Filters/AuthFilter.php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilterAdmin implements FilterInterface
{
    public $methods = [
        'POST' => ['csrf'],
    ];
	
    public function before(RequestInterface $request, $arguments = null)
    {
		// https://shield.codeigniter.com/quick_start_guide/using_authorization/
		$user = auth()->user();

		if($user == NULL)
		{
			return redirect()->to('/');
		}
	
		if (!$user->inGroup('admin')) {	
			return redirect()->to('/home');
		}
		
		//if (!auth()->user()->can('test.control')) {
		//	return redirect()->to('/');
		//}
		
		return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No actions needed after the request
		return null;
    }
}