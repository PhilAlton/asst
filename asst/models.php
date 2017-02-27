<?php
require 'query.php';

class User {



	public static function authenticate(){

		// authenticate user session to enable access to api functions
		$q_auth = false;


		// retrieve stored password string from database against UserName
		$query = New Query(SELECT, 'Password FROM `AuthTable` WHERE `UserName` =:UserName');
//		$password = decrypt($query->execute([':UserName' => $_SERVER["PHP_AUTH_USER"]])[0]["Password"]);						//FIX - decrypt should go in query class
		$password = decrypt($query->execute([':UserName' => $_SERVER["PHP_AUTH_USER"]]));

		// Check if the hash of the entered login password, matches the stored hash.
		if (password_verify
			(base64_encode
				(
					hash('sha384', $_SERVER["PHP_AUTH_PW"], true)
				),
				$password
			))
		{
			// Success :D
			$q_auth = true;
		} else {
			// Failure :(
			http_response_code(401); // not authorised
			$q_auth = false;
		}


		return $q_auth;

	}


	public static function createUser($params){
		// code to create new user, i.e update userTable and create new unique table



// User creation
	// create random SALT when creating a new user, store the SALT, and combine the POST["password"] with the SALT.
	// base64 encode and then HASH using SHA384 the SALT.password combination
	// Encrypt the password using (e.g using an SSL-like key)
	// This key should be obtained from an ini file stored outside the server's accesible areas
	// Store the result in the password column of the database
	// ------->  Generate a random auth token
	// (?) HASH the username (?use a different algorithm), and send this to the user's email as a link

	// Send the Auth Token and hashed username back to the User's device for these to be stored.

	// On clicking the link in the email, the auth token will be registered as verified, and can be used in authenticated transactions
	// Further communication with the API should be via the hashed username and auth token.

		//Ensure user of UserName does not already exist
		$query = New Query(SELECT, '* FROM `AuthTable` WHERE `UserName` =:UserName');
		$conflict = $query->execute([':UserName' => $params['UserName']]);
		if (empty($conflict)){
			echo "database conflict, user {$params['UserName']} alraedy exists";

		} else {

			// Hash a new password for storing in the database.
			// The function automatically generates a cryptographically safe salt.
			$password =	encrypt(
							password_hash
							(
								base64_encode
								(
									hash('sha384', $params['Password'], true)
								),
								PASSWORD_DEFAULT
							)
						);

			// TODO: Devise AuthToken uses and method
			$AuthToken = "randomauthtoken90";


			// Update AuthTable with parameters
			$query = New Query(
							INSERT, "INTO AuthTable".
								"(UserName, Password, AuthToken)".
							"VALUES".
								"(:UserName, :Password, '$AuthToken')"
							);

			$query->execute([':UserName' => $params['UserName'], ':Password' => $password]);

			// Retrieve the created primary key
			$query = New Query(SELECT, '* FROM `AuthTable` WHERE `UserName` =:UserName');
			$uID = $query->execute([':UserName' => $params['UserName']])['UniqueID'];


			// Update UserTable with parameters
			$query = New Query(
					INSERT, "INTO UserTable".
						"(UniqueID, Firstname, Surname, DoB, Gender, Age_Of_Symptom_Onset, Research_Participant, NHS_Number)".
					"VALUES".
						"(:UniqueID, :Firstname, :Surname, :DoB, :Gender, :Age_Of_Symptom_Onset, :Research_Participant, :NHS_Number)"
					);

			$query->execute([':UniqueID' => $uID,
							':Firstname' => $params['Firstname'],
							':Surname' => $params['Surname'],
							':DoB' => $params['DoB'],
							':Gender' => $params['Gender'],
							':Age_Of_Symptom_Onset' => $params['Age_Of_Symptom_Onset'],
							':Research_Participant' => $params['Research_Participant'],
							':NHS_Number' => $params['NHS_Number']]);




	/*
			// Create Data Table for User
			$query = New Query(
							CREATE, "TABLE DATA_TABLE_$uID".
							"(".
								"DataID int NOT NULL".
							")"
							);

			$query->execute();

		*/

		}
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

		$query = New Query(SELECT, '* FROM `AuthTable` WHERE `UserName` =:UserName');
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