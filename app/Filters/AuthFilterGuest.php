<?php
// app/Filters/AuthFilter.php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilterGuest implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
		// https://shield.codeigniter.com/quick_start_guide/using_authorization/
		$user = auth()->user();
        
		if($user != NULL)
		{
			return redirect()->to('/admin');
		}
		
		return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No actions needed after the request
		return null;
    }
}