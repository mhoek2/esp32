<?php

namespace App\Models;

use CodeIgniter\Model;

class Users extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['firstname', 'middlename', 'lastname'];

	// TODO:
	//
	// cleaner to use find, findall overrides for getUser and getUsers?

    public function getUser( $user_id = NULL  )
    {
		return $this->getUsers( $user_id );
    }
	
    public function getUsers( $user_id = NULL )
    {
		$builder = $this->select('users.id, users.username, users.firstname, users.middlename, users.lastname, auth_groups_users.group, auth_identities.secret as email')
					->join('auth_identities', 'auth_identities.user_id = users.id', 'left')
					->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left');
					
		if ( !empty($user_id) && is_int($user_id ) )
			$builder->where('users.id', $user_id);
		
		$items = $builder->findAll();

		foreach( $items as $id => $item ) {
			$items[$id]['shortname'] = generateUserShortName( $item );
			$items[$id]['fullname'] = generateUserFullName( $item );
		}
		
		return $items;
    }	
}

