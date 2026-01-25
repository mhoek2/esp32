<?php
namespace App\Services;

use App\Models\User;

use App\Models\UserMeta;			// for admin debug

/**
 * Provides service to store user meta
 *
 * @example
 * ```
 * $meta = service('user_meta');
 * $meta->save( 'key', 'string/json' );
 * $value = $meta->find( 'key' );
 * ```
 *
 * @package App\Services
 *
 */
class UserMetaService
{
	protected $user;
	protected $user_data;
	protected $user_meta;
	
	
	/**
	 * This method initializes the `User` object, retrieves the user's information, and ensures the user is valid. 
	 * If the user is not valid, the process is halted with an error message. 
	 *
	 * It also initializes the appropriate `user_meta`
	 *
	 * @throws Exception If the user data is invalid or missing, the action will be halted with an error message.
	 */
	public function __construct( )
	{
        $this->user = new User();
		$this->user_data = $this->user->getUserInfo();
		
		if ( !$this->user_data )
			die("invalid action");
		
		$this->user_meta = new UserMeta();
	}
	
	/**
	 * Save a user meta key-value pair.
	 *
	 * Stores a value associated with a specific key. If a user ID is provided, the value is saved for that user. 
	 * If no user ID is provided, the value is associated with the current user session.
	 *
	 * @param string $name The name under which the value will be stored.
	 * @param string $value The value to be stored (preferably in JSON format).
	 * @param int|null $user_id (Optional) The ID of the user. Defaults to NULL (current user session).
	 * 
	 * @return void
	 */
	public function save( string $name, string $value, int $user_id = NULL )
	{
		if ( is_null($user_id) ) 
			$user_id = (int) $this->user_data['id'];
		
		$this->user_meta->replace([ 
			'user_id' 	=> $user_id, 
			'name'		=> $name,
			'value'		=> $value
		]);
	}
	
	/**
	 * Retrieve a meta value for a given user.
	 *
	 * This method fetches the stored value associated with a specific key (name) for the given user. 
	 * If no user ID is provided, it defaults to the current user session.
	 *
	 * @param string $name The name of the key whose value is to be retrieved.
	 * @param int|null $user_id (Optional) The ID of the user. Defaults to NULL (current user session).
	 * 
	 * @return mixed The stored value associated with the specified key, or NULL if not found.
	 */
	public function find( string $name, int $user_id = NULL )
	{
		if ( is_null($user_id) ) 
			$user_id = (int) $this->user_data['id'];
		
		return $this->user_meta->where([
			'user_id'	=> $user_id,
			'name' 		=> $name
		])->first();
	}	
}