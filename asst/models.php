<?php
require 'query.php';

class User {



	public static function authenticate($uID){
		// authenticate user session to enable access to api functions

	}


	public static function createUser(){
		// code to create new user, i.e update userTable and create new unique table



	// User creation
		// create random SALT when creating a new user, store the SALT, and combine the POST["password"] with the SALT.
		// base64 encode and then HASH 256 the SALT.password combination
		// Encrypt the password using (e.g using an SSL-like key)
		// This key should be obtained from an ini file stored outside the server's accesible areas
		// Store the result in the password column of the database
		// Generate a random auth token
		// HASH the username (?use a different algorithm), and send this to the user's email as a link
		
		// Send the Auth Token and hashed username back to the User's device for these to be stored.
				
		// On clicking the link in the email, the auth token will be registered as verified, and can be used in authenticated transactions
		// Further communication with the API should be via the hashed username and auth token.



	}

	public static function handleRequest($method, $uID, $params = null){

		// ?need to instantiate instance of model
		$return;

		switch ($method) {
/*			case 'PUT':
				// call method to replace
				updateParams();
				break;
*/
			case 'POST':
				// call method to update single varaibles
				User::updateParams($uID, $params);
				break;

			case 'DELETE':
				// call method to delete
				User::deleteUser($uID);
				break;

			case 'GET':
				// call method to get
				User::getRepresentation($uID);
				break;

			default:
				// throw exception

		}


		return 'need to return json representation or success string';


	}




	private static function getRepresentation($uID){
		// GET request

		$query = New Query('SELECT * FROM `UserTable` WHERE `UniqueID` =:id');
		Output::setOutput($query->execute([':id' => $uID]));

	}

	private static function updateParams($uID, $params){
		//PUT request, acepting multiple arguments including user ID.


	}


	private static function deleteUser($uID){
		// DELETE request, accepting user ID;

	}




}



class Data {


	public static function syncData(){
		// method to get user data against timestamp and either update (call postData),
		//	or withdraw any additional server data and pass back to user
	}






}


?>