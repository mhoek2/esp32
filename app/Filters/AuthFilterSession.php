<?php
// app/Filters/AuthFilter.php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

use App\Models\User;

class AuthFilterSession implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
		helper(['user']);
		
		// https://shield.codeigniter.com/quick_start_guide/using_authorization/
		$user = auth()->user();

		if($user == NULL)
		{
			return redirect()->to('/');
		}
		
		$user_info	= (new User())->getUserInfo();
		
		if ( !$user->inGroup('admin') && current_url() !== url_to('home')){
			return redirect()->to(url_to('home'));
		}
		
		return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No actions needed after the request
		return null;
    }
}