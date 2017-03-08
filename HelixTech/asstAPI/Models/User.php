<?php namespace HelixTech\asstAPI\Models;
      /**
       * @author Philip Alton
       * @copyright Helix Tech Ltd. 2017
       * @file User.php
       * @package asstAPI
       * 
       * @todo Rewrite models to abstract SQL queries
       * 
       */

    use HelixTech\asstAPI\{Output, Query, Crypt};
    use HelixTech\asstAPI\Exceptions\{UnableToAuthenticateUserCredentials};

    /**
     * Summary of User: class containing static methods to perform actions on the database
     * - Actions associated with a particular user
     */
    class User {

        /** @param User::$uID = UniqueID for connected user (or created user) */
        private static $uID;

	    public static function authenticate(){

		    // authenticate user session to enable access to api functions
		    $q_auth = false;

            try{
	            // retrieve stored password string from database against UserName
	            $query = New Query(SELECT, 'UniqueID, Password FROM `AuthTable` WHERE `UserName` =:UserName');

                $UserDetails = $query->execute([':UserName' => $_SERVER["PHP_AUTH_USER"]]);
                // TO-DO If control block will need to go into query class for null outputs, as this is where decryption will occur
                if (count($UserDetails)===0){
                    // If no password obtained then throw exception and handle.
                    $e = $_SERVER['PHP_AUTH_USER']." DOES NOT EXIST";
                    throw new \UnexpectedValueException($e);
                } else {
                    // Else decrypt the password
                    User::$uID = $UserDetails["UniqueID"];
                    $password = Crypt::decrypt($UserDetails["Password"]);                                                             //FIX - decrypt should go in query class

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


	    /**
         * Summary of createUser: CreateUser function insertes a new record into the master tables, and establishes a User's Data table.
         *
         * create random SALT when creating a new user, store the SALT, and combine the POST["password"] with the SALT.
         * base64 encode and then HASH using SHA384 the SALT.password combination
         * Encrypt the password using (e.g using an SSL-like key)
         * This key should be obtained from an ini file stored outside the server's accesible areas
         * Store the result in the password column of the database
         * ------->  Generate a random auth token
         * (?) HASH the username (?use a different algorithm), and send this to the user's email as a link
         *
         * Send the Auth Token and hashed username back to the User's device for these to be stored.
         *
         * On clicking the link in the email, the auth token will be registered as verified, and can be used in authenticated transactions
         * Further communication with the API should be via the hashed username and auth token.
         *
         *
         *
	     * @param mixed $params - passed in from the POST fields
         *                          These parameters will be sanetised through binding via PDO
         *
         * @uses Output::setOutput() to monitor success
         *
	     */
	    public static function createUser($params){

            $results = array();
		    //Ensure user of UserName does not already exist
		    $query = New Query(SELECT, '* FROM `AuthTable` WHERE `UserName` =:UserName');
		    $conflict = $query->execute([':UserName' => $params['UserName']]);
		    if (count($conflict) !== 0){
			    http_response_code(409);
			    Output::errorMsg("database conflict, user {$params['UserName']} alraedy exists");

		    } else {

			// Hash a new password for storing in the database.
            // The function automatically generates a cryptographically safe salt.
			    $password =	Crypt::encrypt(
							    password_hash
							    (
								    base64_encode
								    (
									    hash('sha384', $params['Password'], true)
								    ),
								    PASSWORD_DEFAULT
							    )
						    );

			    /** @todo: Devise AuthToken uses and method */
			    $AuthToken = "randomauthtoken90";


			    // Update AuthTable with parameters:
			    $query = New Query(
							    INSERT, "INTO AuthTable".
								    "(UserName, Password, AuthToken)".
							    "VALUES".
								    "(:UserName, :Password, '$AuthToken')"
							    );

			    $results = array_merge($results, $query->execute([':UserName' => $params['UserName'], ':Password' => $password]));
                /** @todo sanitize output to remove password and swap for authtoken */


			    // Retrieve the created primary key
			    $query = New Query(SELECT, '* FROM `AuthTable` WHERE `UserName` =:UserName');
			    User::$uID = $query->execute([':UserName' => $params['UserName']])['UniqueID'];


			    // Update UserTable with parameters
			    $query = New Query(
					    INSERT, "INTO UserTable".
						    "(UniqueID, Age, Gender, Age_Of_Symptom_Onset, Research_Participant)".
					    "VALUES".
						    "(:UniqueID, :Age, :Gender, :Age_Of_Symptom_Onset, :Research_Participant)"
					    );

                // Output should be set on the success of the following record insert
			    $results = array_merge($results, ($query->execute([':UniqueID' => User::$uID,
							    ':Age' => User::age($params['DoB']),
							    ':Gender' => $params['Gender'],
							    ':Age_Of_Symptom_Onset' => $params['Age_Of_Symptom_Onset'],
							    ':Research_Participant' => $params['Research_Participant']
						])));



			    // Create General Data Table for User
                $query = New Query(
                    CREATE, "TABLE GEN_DATA_TABLE_".User::$uID.
                    "(".
                        "DataID int(11) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,".
                        "Date date NOT NULL,".
                        "Basdai_1 tinyint UNSIGNED NOT NULL,".
                        "Basdai_2 tinyint UNSIGNED NOT NULL,".
                        "Basdai_3 tinyint UNSIGNED NOT NULL,".
                        "Basdai_4 tinyint UNSIGNED NOT NULL,".
                        "Basdai_5 tinyint UNSIGNED NOT NULL,".
                        "Basdai_6 tinyint UNSIGNED NOT NULL,".
                        "Basdai_Total decimal(4,2) UNSIGNED NOT NULL,".
                        "Overall_Spinal_Pain tinyint UNSIGNED NOT NULL,".
                        "Basfi_1 tinyint UNSIGNED NOT NULL,".
                        "Basfi_2 tinyint UNSIGNED NOT NULL,".
                        "Basfi_3 tinyint UNSIGNED NOT NULL,".
                        "Basfi_4 tinyint UNSIGNED NOT NULL,".
                        "Basfi_5 tinyint UNSIGNED NOT NULL,".
                        "Basfi_6 tinyint UNSIGNED NOT NULL,".
                        "Basfi_7 tinyint UNSIGNED NOT NULL,".
                        "Basfi_8 tinyint UNSIGNED NOT NULL,".
                        "Basfi_9 tinyint UNSIGNED NOT NULL,".
                        "Basfi_10 tinyint UNSIGNED NOT NULL,".
                        "Basfi_Total tinyint UNSIGNED NOT NULL,".
                        "Overall_Spondylitis_Activity tinyint UNSIGNED NOT NULL,".
                        "Flare tinyint(1) NOT NULL,".
                        "Flare_Duration text NULL,".
                        "Areas_Affected text NULL,".
                        "Flare_Freetext text NULL".
                    ")"
                );

			    $query->execute();



                // If the User has agreed to be a reserach participant:
                if ($params['Research_Participant'] == 1){
                    $results = array_merge($results, User::participateResearch(User::$uID, $params));
                }



                Output::setOutput($results);


		    }
	    }


        /**
         * Summary of validateParticipateResearch - queries the database regarding whether uID is registered as a research participant already
         * Will invoke User::participateResearch, to register the User as a research subject.
         * @param mixed User::$uID - UniqueId of the User
         * @param mixed $params - Parameters from the input stream
         * @return array - to be sent back to the client
         */
        public static function validateParticipateResearch($params){

            $results = array();
            $query = New Query(SELECT, 'Research_Participant FROM `UserTable` WHERE `UniqueID` =:UniqueID');
		    $isResearchParticipant = $query->execute([':UniqueID' => User::$uID]);

            if (!$isResearchParticipant){
                $results = array_merge($results, User::participateResearch($params));

            } else {
                $results = array_merge($results, array("Research_Participant" => true));

            }

            return $results;

        }


        public static function participateResearch($params){

            $results = array();
            // Update ResearchTable with parameters
            /** @todo need to update this query to reflect changes to research table, adding baseline survey info */
            $query = New Query(
                    INSERT, "INTO ResearchTable".
                        "(UniqueID, Firstname, Surname, DoB)".
                    "VALUES".
                        "(:UniqueID, :Firstname, :Surname, :DoB)"
                    );

            $results = array_merge($results, ($query->execute([':UniqueID' => User::$uID,
                            ':Firstname' => $params['Firstname'],
                            ':Surname' => $params['Surname'],
                            ':DoB' => $params['DoB']])));


            /** @todo need to amend this query to match true database structure */
            $query = New Query(
                CREATE, "TABLE RCH_DATA_TABLE_".User::$uID.
                "(".
                    "DataID int(11) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,".
                    "Date date,".
                    "Sleep_1 tinyint UNSIGNED NOT NULL,".
                    "Sleep_2 tinyint UNSIGNED NOT NULL,".
                    "Sleep_3 tinyint UNSIGNED NOT NULL,".
                    "Q_Medications_Changed tinyint(1) NOT NULL,".
                    "Medication_Changes text null,".
                    "Currently_Smoking tinyint UNSIGNED NOT NULL".
                ")"
            );

			$query->execute();


            return $results;

        }



	    /**
         * Summary of handleRequest - direct .../Users/{UserName} to the appropiate methdos
	     * @param mixed $method - the HTTP VERB; functionality has been written so far for PUT, DELETE and GET for this endpoint
	     * @param mixed $UserName - UserName as passed in through the HTTP header
	     * @param mixed $params - Parameters passed in from the POST fields
	     * @throws UnableToAuthenticateUserCredentials
         *
	     */
	    public static function handleRequest($method, $UserName, $params){

            try{
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
                    $e = "Failed to validate UserName against Password";
                    throw new UnableToAuthenticateUserCredentials($e);
		        }
            } catch (UnableToAuthenticateUserCredentials $e){
                http_response_code(401);
                Output::errorMsg("Unable to authenticate: ".$e->getMessage().".");

            }



	    }


	    /**
         * Summary of getUser - mapped to endpoint for GET requests to .../Users/{UserName}
         * @param mixed $UserName
         * @see \HelixTech\asstAPI\User::handleRequest()
	     * @return array
	     */
	    private static function getUser($UserName){
		    // GET request
            $results = array();

            //Get info from User's records in both User Data Tables
		    $query = New Query(SELECT, '* FROM `ResearchTable` WHERE `UniqueID` =:UniqueID');
		    $results = array_merge( $results, $query->execute([':UniqueID' => User::$uID]));
            $query = New Query(SELECT, '* FROM `UserTable` WHERE `UniqueID` =:UniqueID');
		    $results = array_merge( $results, $query->execute([':UniqueID' => User::$uID]));

            return $results;

	    }


	    /**
        * Summary of updateParams - mapped to endpoint for PUT requests to .../Users/{UserName}
	     * @param mixed $UserName
	     * @param mixed $params
	     * @return array
	     */
	    private static function updateParams($UserName, $params){
            $results = array();

            // Asign columns in the User Table to an array
                // This could be dynamically created from a call to the Table to show list of columns
            $query = New Query(SELECT, "COLUMN_NAME "
                                    ."FROM INFORMATION_SCHEMA.COLUMNS "
                                    ."WHERE TABLE_NAME=:tableName"
                                    );
            $colArray['ResearchTable'] = $query->execute([':tableName' => 'ResearchTable']);
            $colArray['UserTable'] = $query->execute([':tableName' => 'UserTable']);

            // Loop through each column, and check whether a post variable has been created with that same column name
            // This prevents SQL injuection in the POST array index; bound parameters will prevent injection from the POST array value
            foreach ($colArray as $tableName => $columns){
                foreach ($columns as $col){
                    if (isset($params[$col["COLUMN_NAME"]])){
                        // check whether ResearchParticipant value is true
                        if (($col["COLUMN_NAME"] == "Research_Participant")
                            and
                            ($params['Research_Participant'] == true))
                        {User::validateParticipateResearch($params);}



                        // process the update query
                        $results = array_merge($results, User::updateParam($tableName, $col["COLUMN_NAME"], $params[$col["COLUMN_NAME"]]));
                    }
                }
            }
            return $results;

	    }

        private static function updateParam($tableName, $column, $value){
            //PUT request, acepting multiple arguments including user ID.
            $query = New Query(UPDATE, "$tableName ".
                                "SET $column=:$column ".
                                "WHERE `UniqueID` =:UniqueID");
		    return $query->execute([":$column" => $value,':UniqueID' => User::$uID]);

        }


	    private static function deleteUser($UserName){
		    // DELETE request, accepting user ID;

		    $query = New Query(DROP, "TABLE GEN_DATA_TABLE_".User::$uID);
		    $query->execute();
		    $query = New Query(DELETE, 'FROM `UserTable` WHERE `UniqueID` =:UniqueID');
		    $query->execute([':UniqueID' => User::$uID]);
		    $query = New Query(DELETE, 'FROM `ResearchTable` WHERE `UniqueID` =:UniqueID');
            $query->execute([':UniqueID' => User::$uID]);
		    $query = New Query(DELETE, 'FROM `AuthTable` WHERE `UniqueID` =:UniqueID');
		    return $query->execute([':UniqueID' => User::$uID]);

        }


        public static function age($DoB){
            $from = new \DateTime($DoB);
            $to   = new \DateTime('today');
            $age = $from->diff($to)->y;

            return $age;
        }

        public static function resetPassword($UserName){

            // function to restet password
            Output::setOutput('function currently not available');

            // Gather identity data or security questions
                // Needs to be enacted prior
                // esoteric questions: e.g. hash username and store as password

            // Verify security questions
            // Send a token over a side-channel
            // Allow user to change password (in the existing session)
            // Logging and auditing password change attempts


        }




    }


?>