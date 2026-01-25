<?php

namespace App\Models;

use CodeIgniter\Model;

use App\Models\TrainingUsers;

class User extends Model
{
	private function getFullUserData( $userId = null )
	{
		$userId = $userId ?? auth()->id();

		return \Config\Database::connect()->table('users')
			->select('users.*, auth_identities.secret as email')
			->join('auth_identities', 'auth_identities.user_id = users.id')
			->where('auth_identities.type', 'email_password')
			->where('users.id', $userId)
			->get()
			->getRowArray();
	}
	
    public function getUserInfo() {
        $user = auth()->user();

        if (!$this->isLoggedIn())
            return false;

        //$data = $user->toRawArray();
        $data = $this->getFullUserData( auth()->id() );
		
        $data["shortname"] = generateUserShortName( $data );
        $data["fullname"] = generateUserFullName( $data );

        $data["is_admin"] = $this->isAdmin();
		
        return($data);
    }
	
    public function isLoggedIn()
    {
        $user = auth()->user();

        if (is_null($user))
            return false;

        // Check if user is logged in by verifying session data
        if (session()->has('user_id'))
            return false;

        return true;
    }

    public function isAdmin()
    {
        if (!$this->isLoggedIn())
            return false;

        if (!auth()->user()->inGroup('admin'))
            return false;

        return true;
    }
}

