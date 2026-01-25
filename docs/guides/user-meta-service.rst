User Meta Service
=================

Quick overview on to use the user meta serice

Use-case
-------
The ability to store user specific data is nice to have.
To do this, a simple yet effective method is to have dedicated a database table and logic to store or retrieve this data.

This is simply using a key/value pair, combined with the user id.

Example usage
--------------
.. code-block:: php
	// load the service
	$meta = service('user_meta');
	
	// store info for current user, string, preferably JSON format
	$meta->save( 'key', 'string/json' );
	
	// retrieve the info using the key
	$value = $meta->find( 'key' );	