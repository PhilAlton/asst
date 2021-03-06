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

    use HelixTech\asstAPI\{Output, Connection, Query, Crypt};
    use HelixTech\asstAPI\Exceptions\{UnableToAuthenticateUserCredentials, RequestPasswordResetForNonExistantUser, AttemptedUseOfExpiredPasswordResetToken, AttemptedPasswordResetWithInvalidGUIDE};



    /**
     * Summary of User: class containing static methods to perform actions on the database
     * - Actions associated with a particular user
     */
    class User {

        /** @param User::$uID = UniqueID for connected user (or created user) */
        public static $uID;



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

			try{
				//Database::instance()->beginTransaction();
				$results = array();
				//Ensure user of UserName does not already exist
				$query = New Query(SELECT, '* FROM `AuthTable` WHERE `UserName` =:UserName');
				$conflict = $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UserName' => $params['UserName']]);
				if (count($conflict) !== 0){
					http_response_code(409);
					Output::errorMsg("database conflict, user {$params['UserName']} alraedy exists");

				} else {

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

					$length = 20; // Length of auth token
					$AuthToken = $params['UserName']."=".bin2hex(random_bytes($length));
					$protectedAuthToken = 
									password_hash
									(
										base64_encode
										(
											hash('sha384', $AuthToken, true)
										),
										PASSWORD_DEFAULT
									); 


					//Encrypt and hash secret questions and asnwers
					$secA1 = password_hash(base64_encode(hash('sha384', $params['SecretAnswer1'], true)),PASSWORD_DEFAULT);                            
					$secA2 = password_hash(base64_encode(hash('sha384', $params['SecretAnswer2'], true)),PASSWORD_DEFAULT);
					$secQ1 = "Question:".$params['SecretQuestion1']."Answer:".$secA1;
					$secQ2 = "Question:".$params['SecretQuestion2']."Answer:".$secA2;


					// Update AuthTable with parameters:
					$query = New Query(
									INSERT, "INTO AuthTable".
										"(UserName, Password, AuthToken, AuthTokenPlain, SecQ1, SecQ2)".
									"VALUES".
										"(:UserName, :Password, :AuthToken, :AuthTokenPlain, :secQ1, :secQ2)"
									);

													/*$query = New Query(
																	LOCK, "TABLES AuthTable WRITE; INSERT INTO AuthTable".
																		"(UserName, Password, AuthToken, AuthTokenPlain, SecQ1, SecQ2)".
																	"VALUES".
																		"(:UserName, :Password, :AuthToken, :AuthTokenPlain, :secQ1, :secQ2);".
																	"UNLOCK TABLES"
																	);
													*/
						

					$results = array_merge($results, $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UserName' => $params['UserName'], ':Password' => $password, ':AuthToken' => $protectedAuthToken, ':AuthTokenPlain'=>$AuthToken,':secQ1' => $secQ1, ':secQ2' => $secQ2]));
					unset($results['secQ1']);
					unset($results['secQ2']);
				
					// Retrieve the created primary key
					$query = New Query(SELECT, '* FROM `AuthTable` WHERE `UserName` =:UserName');
					User::$uID = $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UserName' => $params['UserName']])['UniqueID'];
				
					// Change password returned to authtoken led by username
					$results['AuthToken'] = $AuthToken;
					unset($results['AuthTokenPlain']);
					unset($results['Password']);
                

					// Update UserTable with parameters
					$query = New Query(
							INSERT, "INTO UserTable".
								"(UniqueID, Age, Gender, Age_Of_Symptom_Onset, Research_Participant)".
							"VALUES".
								"(:UniqueID, :Age, :Gender, :Age_Of_Symptom_Onset, :Research_Participant)"
							);

					// Output should be set on the success of the following record insert
					$results = array_merge($results, ($query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [
									':UniqueID' => User::$uID,
									':Age' => User::age($params['DoB']),
									':Gender' => $params['Gender'],
									':Age_Of_Symptom_Onset' => $params['Age_Of_Symptom_Onset'],
									':Research_Participant' => $params['Research_Participant']
							])));



					// Create General Data Table for User
					$query = New Query(
						CREATE, "TABLE GEN_DATA_TABLE_".User::$uID.
						"(".
							"GenDataID int(11) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,".
							"Date date NOT NULL UNIQUE,".
							"LastUpdate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,".
							"Basdai_1 TEXT NOT NULL,".
							"Basdai_2 TEXT NOT NULL,".
							"Basdai_3 TEXT NOT NULL,".
							"Basdai_4 TEXT NOT NULL,".
							"Basdai_5 TEXT NOT NULL,".
							"Basdai_6 TEXT NOT NULL,".
							"Basdai_Total TEXT NOT NULL,".
							"Overall_Spinal_Pain TEXT NOT NULL,".
							"Basfi_1 TEXT NOT NULL,".
							"Basfi_2 TEXT NOT NULL,".
							"Basfi_3 TEXT NOT NULL,".
							"Basfi_4 TEXT NOT NULL,".
							"Basfi_5 TEXT NOT NULL,".
							"Basfi_6 TEXT NOT NULL,".
							"Basfi_7 TEXT NOT NULL,".
							"Basfi_8 TEXT NOT NULL,".
							"Basfi_9 TEXT NOT NULL,".
							"Basfi_10 TEXT NOT NULL,".
							"Basfi_Total TEXT NOT NULL,".
							"ESR TEXT NOT NULL,".
							"CRP TEXT NOT NULL,".
							"ASDAS_Total TEXT NOT NULL,".
							"Overall_Spondylitis_Activity TEXT NOT NULL,".
							"Flare TEXT NOT NULL,".
							"Flare_Duration text NULL,".
							"Areas_Affected text NULL,".
							"Flare_Freetext text NULL".
						")"
					);

					$query->execute(1);



					// If the User has agreed to be a reserach participant:
					if ($params['Research_Participant'] == 1){
						$results = array_merge($results, User::participateResearch($params));
					}



					Output::setOutput($results);


				}

			} catch (\Exception $e) {
				echo $e->getMessage();
				User::deleteUser($params['UserName']);
				throw new \Exception($e->getMessage());
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
		    $isResearchParticipant = $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UniqueID' => User::$uID]);

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
            $query = New Query(
                    INSERT, "INTO ResearchTable".
                        "(UniqueID, Firstname, Surname, DoB)".
                    "VALUES".
                        "(:UniqueID, :Firstname, :Surname, :DoB)"
                    );

            $results = array_merge($results, ($query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [
							':UniqueID' => User::$uID,
                            ':Firstname' => $params['Firstname'],
                            ':Surname' => $params['Surname'],
                            ':DoB' => $params['DoB']
							]
						)));




			$query = New Query(
            CREATE, "TABLE RCH_DATA_TABLE_".User::$uID.
                "(".
                    "RchDataID int(11) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,".
                    "Date date NOT NULL UNIQUE,".
                    "LastUpdate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,".
                    "Sleep_1 TEXT NOT NULL,".
                    "Sleep_2 TEXT NOT NULL,".
                    "Sleep_3 TEXT NOT NULL,".
                    "Q_Medications_Changed TEXT NOT NULL,".
                    "Medication_Changes text null,".
                    "Currently_Smoking TEXT NOT NULL".
                ")"
            );

			$query->execute(1);


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
		        if (Connection::authenticate()){

			        switch ($method) {
				        /*			case 'POST':
				        // call method to replace entire user / ?create new user
				        updateParams();
				        break;
                         */
				        case 'PUT':
					        // call method to update single varaibles
                            Output::setOutput(User::updateParams($params));
					        break;

				        case 'DELETE':
					        // call method to delete
					        Output::setOutput(User::deleteUser());
					        break;

				        case 'GET':
					        // call method to get
					        Output::setOutput(User::getUser());
					        break;

				        default:
                            Output::errorMsg("HTML verb has no corisponding API action");
				        // throw exception

			        }

		        } else {
			        Output::setOutput('Invalid Username\Password Combination');
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
         * @see \HelixTech\asstAPI\User::handleRequest()
	     * @return array
	     */
	    private static function getUser(){
		    // GET request
            $results = array();

            //Get info from User's records in both User Data Tables
			$query = New Query(SELECT, 'AuthTokenPlain FROM `AuthTable` WHERE `UniqueID` = :UniqueID');
			$results = array_merge( $results, Array("AuthToken" => $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UniqueID' => User::$uID])));
		    $query = New Query(SELECT, '* FROM `ResearchTable` WHERE `UniqueID` =:UniqueID');
		    $results = array_merge( $results, $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UniqueID' => User::$uID]));
            $query = New Query(SELECT, '* FROM `UserTable` WHERE `UniqueID` =:UniqueID');
		    $results = array_merge( $results, $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UniqueID' => User::$uID]));

            return $results;

	    }


	    /**
        * Summary of updateParams - mapped to endpoint for PUT requests to .../Users/{UserName}
	     * @param mixed $params
	     * @return array
	     */
	    private static function updateParams($params){
            $results = array();

            // Asign columns in the User Table to an array
                // This could be dynamically created from a call to the Table to show list of columns
            $query = New Query(SELECT, "COLUMN_NAME "
                                    ."FROM INFORMATION_SCHEMA.COLUMNS "
                                    ."WHERE TABLE_NAME=:tableName"
                                    );
            $colArray['ResearchTable'] = $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':tableName' => 'ResearchTable']);
            $colArray['UserTable'] = $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':tableName' => 'UserTable']);

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
		    return $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [":$column" => $value,':UniqueID' => User::$uID]);

        }


	    private static function deleteUser(){		
		    // DELETE request, accepting user ID;				

            $query = New Query(SELECT, 'Research_Participant FROM `UserTable` WHERE `UniqueID` =:UniqueID');
		    $isRchParticipant = $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UniqueID' => User::$uID]);
            if ($isRchParticipant){
                $query = New Query(DROP, "TABLE RCH_DATA_TABLE_".User::$uID);
		        try{$query->execute(1);} catch (\Exception $e){}
            }
		    $query = New Query(DROP, "TABLE GEN_DATA_TABLE_".User::$uID);
		    try{$query->execute(1);} catch (\Exception $e){}
		    $query = New Query(DELETE, 'FROM `UserTable` WHERE `UniqueID` =:UniqueID');
		    try{$query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UniqueID' => User::$uID]);} catch (\Exception $e){}
		    $query = New Query(DELETE, 'FROM `ResearchTable` WHERE `UniqueID` =:UniqueID');
            try{$query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UniqueID' => User::$uID]);} catch (\Exception $e){}
		   
		   $query = New Query(DELETE, 'FROM `AuthTable` WHERE `UniqueID` =:UniqueID');
		   return $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UniqueID' => User::$uID]);

        }


        public static function age($DoB){
            $from = new \DateTime($DoB);
            $to   = new \DateTime('today');
            $age = $from->diff($to)->y;

            return $age;
        }



		public static function resetProceed($UserName, $input){
		
			try{
			//	Output::setOutput($input);
			//	echo "new things here";
				$output = Array();
				if(!isset($input['part'])){$input['part']="undefined";}
			
			
				switch ($input['part']) {
				
					case 'checkGUIDE':
						// database call 
						$query = New Query(SELECT, 'PasswordResetToken, PasswordResetTokenExpiry, SecQ1, SecQ2  FROM `AuthTable` WHERE `UserName` =:UserName');
						$results = $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UserName' => $UserName]);


						// check GUIDE $input['GUIDE'] matches GUIDE
						$uniqueCode = $results["PasswordResetToken"];
						if ($input['GUIDE'] === $uniqueCode) { 
							// check GUIDE is in-date
							$now = new \DateTime(); //current date/time
							$tokenExpiry = new \DateTime($results["PasswordResetTokenExpiry"]);

							if ($now < $tokenExpiry){
								// output secret questions
								$s1 = explode("Answer:", $results["SecQ1"]);
								$sq1 = ltrim($s1[0],"Question:"); // secret question
								$output['SecretQuestion1'] = $sq1;

								$s2 = explode("Answer:", $results["SecQ2"]);
								$sq2 = ltrim($s2[0],"Question:"); // secret question
								$output['SecretQuestion2'] = $sq2;

							} else {
								http_response_code(401);
								throw new AttemptedUseOfExpiredPasswordResetToken("Password reset token has expired for user: ".$UserName);
							}
						} else {
							throw new AttemptedPasswordResetWithInvalidGUIDE("Token does not match the stored PasswordResetToken, for user: ".$UserName);
						}
						break;
				
					case 'checkAnswers':
						// database call 
						$query = New Query(SELECT, 'SecQ1, SecQ2  FROM `AuthTable` WHERE `UserName` =:UserName');
						$results = $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UserName' => $UserName]);

						$s1 = explode("Answer:", $results["SecQ1"]);
						$sq1 = ltrim($s1[0],"Question:"); // secret question
						$sa1 = $s1[1]; // secret answer

						$s2 = explode("Answer:", $results["SecQ2"]);
						$sq2 = ltrim($s2[0],"Question:"); // secret question
						$sa2 = $s2[1]; // secret answer

						// check secret answers $input['secretAnswer1'] and $input['secretAnswer2'] match database call
			            if (password_verify(
								base64_encode(hash('sha384', $input[$sq1], true)),
								$sa1
							) and password_verify(
								base64_encode(hash('sha384', $input[$sq2], true)),
								$sa2
							)){

							//set database to validate GUIDE
							$query = New Query(UPDATE, "`AuthTable` ".
									"SET `PasswordResetVerified`=1 ".
									"WHERE  `UserName` =:UserName");
							$query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UserName' => $UserName]);

							$output['secretAnswersChecked'] = true;

						} else {
							// Log whether secre questions and answers match
							$output['secretAnswersChecked'] = false;
							throw new SecretAnswersInvalid("Secret answers given do not match those stored for ".$UserName);
							// this error should generate an alert, if there is much activity against it
							// However, this level of analytics will need to fall in to the analytics class
							// And not form part of the error's own class
						}

						break;

					case 'newPassword':
						// database call 
						$query = New Query(SELECT, 'PasswordResetToken, PasswordResetVerified, PasswordResetTokenExpiry FROM `AuthTable` WHERE `UserName` =:UserName');
						$results = $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UserName' => $UserName]);

						// check GUIDE $input['GUIDE'] matches GUIDE
						$uniqueCode = $results["PasswordResetToken"];
						if ($input['GUIDE'] === $uniqueCode) { 
							// check GUIDE is in-date
							$now = new \DateTime(); //current date/time
							$tokenExpiry = new \DateTime($results["PasswordResetTokenExpiry"]);

							if ($now < $tokenExpiry){
								
								if ($results["PasswordResetVerified"]){
								
									// Retrieve new password, hash appropiately and store in database
									$newPass = $input['newPassword'];
								
									// Hash a new password for storing in the database.
									// The function automatically generates a cryptographically safe salt.
									$newHashedPass =
													password_hash
													(
														base64_encode
														(
															hash('sha384', $newPass, true)
														),
														PASSWORD_DEFAULT
													);

									$length = 20; // Length of auth token
									$AuthToken = $UserName."=".bin2hex(random_bytes($length));
									$protectedAuthToken = 
													password_hash
													(
														base64_encode
														(
															hash('sha384', $AuthToken, true)
														),
														PASSWORD_DEFAULT
													); 



									// Reset password resettting info as well as setting new password
									$query = New Query(UPDATE, "`AuthTable` ".
											"SET `Password`=:Password, `AuthToken`=:AuthToken, `AuthTokenPlain`=:AuthTokenPlain, `PasswordResetVerified`=0, `PasswordResetTokenExpiry`=:PassResTokEx, `PasswordResetAttempts`=0, `PasswordResetToken`=NULL ".
											"WHERE `UserName` =:UserName");
									$query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [":Password" =>$newHashedPass, ":AuthToken" => $protectedAuthToken, ":AuthTokenPlain" => $AuthToken, ":PassResTokEx" => $now->format('Y-m-d H:i:s'), ':UserName' => $UserName]);
									

								
									$output['passwordResetComplete'] = true;


								} else {
									throw new AttemptedNewPasswordWithoutSecretAnswers("Password reset attempted bypassing secret questions for user: ".$UserName);
								}
							} else {
								throw new AttemptedUseOfExpiredPasswordResetToken("Password reset token has expired for user: ".$UserName);
							}
						} else {
							throw new AttemptedPasswordResetWithInvalidGUIDE("Token does not match the stored PasswordResetToken, for user: ".$UserName);
						}
						

						break;				


					default:
						# code...
						$output[$input];	// for debugging
						//http_response_code('500');
						break;

				}

				Output::setOutput($output);

			} catch (AttemptedUseOfExpiredPasswordResetToken $e){
				http_response_code(401);
				Output::errorMsg("Password reset token expired. ".$e->getMessage());

			} catch (AttemptedPasswordResetWithInvalidGUIDE $e){
				http_response_code(401);
				Output::errorMsg("Password reset token invalid. ".$e->getMessage());

			} catch (SecretAnswersInvalid $e){
				http_response_code(401);
				Output::errorMsg("Invalid secret answers given. ".$e->getMessage());

			} catch (AttemptedNewPasswordWithoutSecretAnswers $e){
				http_response_code(401);
				Output::errorMsg("Invalid password reset");

			}

		}


        public static function resetPassword($UserName){

			try{
				// Accept submitted username and validate this	
				$query = New Query(SELECT, 'UniqueID FROM `AuthTable` WHERE `UserName` =:UserName');
				$uniqueID = $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UserName' => $UserName]);

				if (count($uniqueID)>0){

					//Generate password reset code
					$prefix = rand(2,34).($uniqueID+17)*3;
					$uniqueCode = uniqid("l".$prefix."f");

					//Generate expiary
					$hours=12;
					$now = new \DateTime(); //current date/time
					$now->add(new \DateInterval("PT{$hours}H"));
					$expiary = $now->format('Y-m-d H:i:s');

					//Store in the database
					$query = New Query(UPDATE, "`AuthTable` ".
									"SET `PasswordResetToken`=:PassResTok, `PasswordResetTokenExpiry`=:PassResTokEx, `PasswordResetAttempts`=`PasswordResetAttempts`+1 ".
									"WHERE `UniqueID` =:UniqueID");
					$query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [":PassResTok" => $uniqueCode, ":PassResTokEx" => $expiary, ':UniqueID' => $uniqueID]);

					

					// Send an email to the user containing the unique link
					$message = 'Please click the following link to reset your password:' . "\r\n"
								."https://axspa.org.uk/passwordReset.html?".urlencode("username=".$UserName."&GUIDE=".$uniqueCode) . "\r\n\r\n"
						//		."debug: uniqueID=" . $uniqueID . "\r\n\r\n"
						//		."debug: PassResTokEx=" . $expiary . "\r\n\r\n"
								. "Please note, this link will expire in 12 hours";
					
					$headers = 'From: ResetPassword@axspa.org.uk' . "\r\n" .
								'Reply-To: ResetPassword@axspa.org.uk';

					mail($UserName, 'Ankylosing Spondylitis Symptom Tracker - Request to Reset Password', $message, $headers);


				} else {
					// error code if no such user: throw error and log
					throw new RequestPasswordResetForNonExistantUser("Password Reset Requested for non existant user: ".$UserName);
					
				}
			} catch (RequestPasswordResetForNonExistantUser $e){

				// in case error needs to be handled in the future


			}

        }




    }


?>
