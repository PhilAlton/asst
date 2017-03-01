<?php
require 'query.php';

class User {



	public static function authenticate(){

		// authenticate user session to enable access to api functions
		$q_auth = false;

        try{
	        // retrieve stored password string from database against UserName
	        $query = New Query(SELECT, 'Password FROM `AuthTable` WHERE `UserName` =:UserName');
	        $password = $query->execute([':UserName' => $_SERVER["PHP_AUTH_USER"]]);

            // TO-DO If control block will need to go into query class for null outputs, as this is where decryption will occur
            if (count($password)===0){
                // If no password obtained then throw exception and handle.
                $e = $_SERVER['PHP_AUTH_USER']." DOES NOT EXIST";
                throw new UnexpectedValueException($e);
            } else {
                // Else decrypt the password
                $password = decrypt($password);                                                             //FIX - decrypt should go in query class

            }

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
        } catch (UnexpectedValueException $e) {
            http_response_code(404);
            Output::errorMsg("Unexpected Value: ".$e->getMessage().".");
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
		if (count($conflict) !== 0){
			http_response_code(409);
			Output::errorMsg("database conflict, user {$params['UserName']} alraedy exists");

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


			// Update AuthTable with parameters:
			$query = New Query(
							INSERT, "INTO AuthTable".
								"(UserName, Password, AuthToken)".
							"VALUES".
								"(:UserName, :Password, '$AuthToken')"
							);

			$result[] = $query->execute([':UserName' => $params['UserName'], ':Password' => $password]);

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

            // Output should be set on the sucess of the following record insert
			$result[] = ($query->execute([':UniqueID' => $uID,
							':Firstname' => $params['Firstname'],
							':Surname' => $params['Surname'],
							':DoB' => $params['DoB'],
							':Gender' => $params['Gender'],
							':Age_Of_Symptom_Onset' => $params['Age_Of_Symptom_Onset'],
							':Research_Participant' => $params['Research_Participant'],
							':NHS_Number' => $params['NHS_Number']]));



			// Create Data Table for User
			$query = New Query(
							CREATE, "TABLE DATA_TABLE_$uID".
							"(".
								"DataID int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,".
								"TimeStamp TIMESTAMP,".		// this might not be the correct way
								"Date date,".
								"Item_1 int(11),".
								"Item_X TEXT".
							")"
							);

			$result[] = $query->execute();

            Output::setOutput($result);


		}
	}

	public static function handleRequest($method, $UserName, $params){


		if (User::authenticate($UserName, $params)){

			switch ($method) {
				/*			case 'POST':
				// call method to replace entire user / ?create new user
				updateParams();
				break;
				 */
				case 'PUT':
					// call method to update single varaibles
                    Output::setOutput(User::updateParams($UserName, $params));
					break;

				case 'DELETE':
					// call method to delete
					Output::setOutput(User::deleteUser($UserName));
					break;

				case 'GET':
					// call method to get
					Output::setOutput(User::getUser($UserName));
					break;

				default:
                    Output::errorMsg("HTML verb has no corisponding API action");
				// throw exception

			}

		//	Output::setOutput('need to return json representation or success string');

		} else {
			Output::setOutput('unable to authenticate');
		}




	}


    public static function resetPassword($UserName){

        // function to restet password
        Output::setOutput('function currently not available');

    }


	private static function getUser($UserName){
		// GET request

		$query = New Query(SELECT, '`UniqueID` FROM `AuthTable` WHERE `UserName` =:UserName');
		$uID = $query->execute([':UserName' => $UserName]);
		$query = New Query(SELECT, '* FROM `UserTable` WHERE `UniqueID` =:uID');
		return $query->execute([':uID' => $uID]);


	}

	private static function updateParams($UserName, $params){

        // Get UserID
		$query = New Query(SELECT, '`UniqueID` FROM `AuthTable` WHERE `UserName` =:UserName');
        $uID = $query->execute([':UserName' => $UserName]);

        // Asign columns in the User Table to an array
            // This could be dynamically created from a call to the Table to show list of columns
        $query = New Query(SELECT, "COLUMN_NAME "
                                ."FROM INFORMATION_SCHEMA.COLUMNS "
                                ."WHERE TABLE_NAME=:tableName"
                                );
        $UserTable_ColArray = $query->execute([':tableName' => 'UserTable']);

        // Loop through each column, and check whether a post variable has been created with that same column name
        // This prevents SQL injuection in the POST array index; bound parameters will prevent injection from the POST array value
        foreach ($UserTable_ColArray as $col){
            if (isset($params[$col["COLUMN_NAME"]])){
                $return[] = User::updateParam($uID, $col["COLUMN_NAME"], $params[$col["COLUMN_NAME"]]);
            }
        }

        return $return;

	}

    private static function updateParam($uID, $column, $value){
        //PUT request, acepting multiple arguments including user ID.
        $query = New Query(UPDATE, "`UserTable` ".
                            "SET $column=:$column ".
                            "WHERE `UniqueID` =:uID");
		return $query->execute([":$column" => $value,':uID' => $uID]);

    }


	private static function deleteUser($UserName){
		// DELETE request, accepting user ID;
		$query = New Query(SELECT, '* FROM `AuthTable` WHERE `UserName` =:UserName');
		$uID = $query->execute([':UserName' => $UserName])['UniqueID'];

		$query = New Query(DROP, "TABLE DATA_TABLE_$uID");
		$query->execute();
		$query = New Query(DELETE, 'FROM `UserTable` WHERE `UniqueID` =:uID');
		$query->execute([':uID' => $uID]);
		$query = New Query(DELETE, 'FROM `AuthTable` WHERE `UniqueID` =:uID');
		return $query->execute([':uID' => $uID]);

    }




}



class Data {


	public static function syncData(){
		// method to get user data against timestamp and either update (call postData),
		//	or withdraw any additional server data and pass back to user
	}






}


?>