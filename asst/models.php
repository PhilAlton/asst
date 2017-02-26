<?php
require 'query.php';

class User {



	public static function authenticate($UserName, $params){

		// authenticate user session to enable access to api functions
		$q_auth = false;


		$query = New Query('SELECT * FROM `AuthTable` WHERE `UserName` =:UserName');
		Output::setOutput($query->execute([':UserName' => $UserName]));

		// Check if the hash of the entered login password, matches the stored hash.
		if (password_verify
			(
				base64_encode
				(
					hash('sha256', $params['password'], true)
				),
				$stored
			)) {
			// Success :D
			$q_auth = true;
		} else {
			// Failure :(
			$q_auth = false;
		}


		return $q_auth;

	}


	public static function createUser($params){
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


		// Hash a new password for storing in the database.
		// The function automatically generates a cryptographically safe salt.
		$password =	password_hash
					(
						base64_encode
						(
							hash('sha384', $params['Password'], true)
						),
						PASSWORD_DEFAULT
					);




		$query = New Query(
						"INSERT INTO AuthTable".
							"(UserName, Password, AuthToken".
						"VALUES".
							"(:UserName, :Password, $AuthToken)"
						);

		$query->execute([':UserName' => $params['UserName'], ':Password' => $password]);

/*

		$query = New Query(
						"CREATE TABLE DATA_TABLE_$uID".
						"(".
							"DataID int NOT NULL".
						")"
						);

		$query->execute();

	*/

	}

	public static function handleRequest($method, $UserName, $params){


		if (User::authenticate($UserName, $params)){

			switch ($method) {
				/*			case 'PUT':
				// call method to replace
				updateParams();
				break;
				 */
				case 'POST':
					// call method to update single varaibles
					User::updateParams($UserName, $params);
					break;

				case 'DELETE':
					// call method to delete
					User::deleteUser($UserName);
					break;

				case 'GET':
					// call method to get
					User::getUser($UserName);
					break;

				default:
				// throw exception

			}

		//	Output::setOutput('need to return json representation or success string');

		} else {
			Output::setOutput('unable to authenticate');
		}




	}




	private static function getUser($UserName){
		// GET request

		$query = New Query('SELECT * FROM `AuthTable` WHERE `UserName` =:UserName');
		Output::setOutput($query->execute([':UserName' => $UserName]));

	}

	private static function updateParams($UserName, $params){
		//PUT request, acepting multiple arguments including user ID.


	}


	private static function deleteUser($UserName){
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