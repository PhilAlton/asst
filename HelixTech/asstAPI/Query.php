<?php namespace HelixTech\asstAPI;

    use HelixTech\asstAPI\{Database, Crypt};
    use Defuse\Crypto\Exception as Ex;

    //require 'database.php';
    define("INSERT", "INSERT");
    define("UPDATE", "UPDATE");
    define("SELECT", "SELECT");
    define("CREATE", "CREATE");
    define("DELETE", "DELETE");
    define("DROP", "DROP");
	define('SIMPLIFY_QUERY_RESULTS_ON', 'SIMPLIFY_QUERY_RESULTS_ON');
	define('SIMPLIFY_QUERY_RESULTS_OFF', 'SIMPLIFY_QUERY_RESULTS_OFF');




    class Query {

        private $query;
        private $database;
	    private $queryType;
        private $encryptedTableBoolean;
        private $unencryptedTableNames = Array("cache", "ConnectionLog", "INFORMATION_SCHEMA");
        private $unencryptedParameters = Array(":UserName", ":UniqueID", ":Research_Participant", ":remoteLastUpdate", ":Date", ":date", "passResTokEx",
												"UserName", "UniqueID", "PasswordResetTokenExpiry", "Research_Participant", "LastUpdate", "date", "Date", "GenDataID", "RchDataID",
												"PasswordResetVerified", "PasswordResetAttempts", "Rch_Data_Count");

        public function lastInsertId(){
            return $this->database->lastInsertId();
        }


        public function __construct($queryType, $query){
            $this->database = Database::instance();
		    $this->queryType = $queryType;
            $this->query = $queryType." ".$query;
            
            // based on table name, decide whether to encrpyt the data
            $this->encryptedTableBoolean = true;
            foreach ($this->unencryptedTableNames as $tableName){
                if(strpos($query, $tableName) !== false){
                    $this->encryptedTableBoolean = false;
                } 
            }
        }


        public function executeMultiTableQuery($params = null){
            $this->database->setToFetchColumnsWithTableNames();
            $return = $this->execute(SIMPLIFY_QUERY_RESULTS_ON,  $params);
            $this->database->setToFetchColumnsWithoutTableNames();
            return $return;
        }


        public function silentexecute($simplifyQueryResults, $params = null){
            $this->buildQuery($params);
            try {
                $this->database->execute();
            } catch (Exception $e) {
                http_response_code(406);
                Output::errorMsg(
                    "caught exception: ".$e->getMessage()
                    ." - with SQL Statement ".$this->query
                    ." and these parameters:".json_encode($params)
                );
            }
        }

        private function handleParamEncryption($param, $value){
            // Encrypt parameters here
            // Exclude specified list of parameters and specified list of table calls
            $newValue;
            $encryptBool = true;

            if($this->encryptedTableBoolean){

                foreach ($this->unencryptedParameters as $unencryptedParam){
                    if($param == $unencryptedParam){
                        $encryptBool = false;
                    } 
                }
            } else {
                $encryptBool = false;
            } 

            if($encryptBool){
                $newValue = Crypt::encrypt($value);
            } else {
                $newValue = $value;
            }
            echo "</br>Param: ".$param." and newvalue:".$newValue;
            return $newValue;
        }

        public function buildQuery($params = null){

            $this->database->query($this->query);
		    if (isset($params)){
			    foreach ($params as $param => $value){				// Pass parameters to PDO statement
				    $this->database->bind(
					    $param,							
					    $this->handleParamEncryption($param, $value)
    				    );
			    }
		    }
        }

        /**
         * Summary of execute - execute a query taking in parameters to bind the SQL statement prepared in the constructor
         *
         * @param mixed $params to be bound into the query
         * @return mixed,
         */
        public function execute($simplifyQueryResults, $params = null){

            $this->buildQuery($params);
			var_dump($this->database->debugDumpParams());
            try {
                $this->database->execute(1);

				if (isset($params)){
					foreach ($params as $key => $value){
					$results[substr($key,1)] = $value;
					}
				} else {
				    $results = false;
				}
				

                switch ($this->queryType){
			        case SELECT:
				        $results = $this->database->resultset();

						// algorithm to decrypt all database output
                        if ($this->encryptedTableBoolean){
                            array_walk_recursive($results, function(&$value, $key){
                                try{    
									if(isset($value) && $value != ""){
										$encryptBool = true;
										foreach ($this->unencryptedParameters as $unencryptedParam){
											if($key == $unencryptedParam){$encryptBool = false;} 
										}
										if($encryptBool){$value = Crypt::decrypt($value);}
									}
                                } catch (Ex\WrongKeyOrModifiedCiphertextException $ex) {
                                    $value = $value;
                                }
                            });
                        }

						// reduce output in case of single row, or single result
						if ($simplifyQueryResults == "SIMPLIFY_QUERY_RESULTS_ON"){
							if (count($results) == 1)
							{
								foreach ($results as $result){$results = $result;}
								if (count($results) == 1){foreach ($results as $result){$results = $result;}}
							}
						}


                        http_response_code(200); // OK
				        break;

			        case INSERT:
				        http_response_code(201); // content created
				        break;

                    case CREATE:
                        $results = "sucess";
				        http_response_code(201); // content created
				        break;

			        case UPDATE:
				        http_response_code(200); // No content *(request fulfilled)
				        break;

			        case DELETE:
						$results = "DELETE sucessful";
				        http_response_code(200); // No content *(request fulfilled)
				        break;

			        case DROP:
						$results = "DROP table sucessful";
				        http_response_code(200); // No content *(request fulfilled)
				        break;

			        default:
				        http_response_code(200);
		        }

            } catch (Exception $e) {

			$results = Array("failed");
            foreach ($results as $param => $value){
                $results[$param] = "failed";
            }

            http_response_code(406);
            Output::errorMsg("caught exception: ".$e->getMessage()
                                ." - with SQL Statement ".$this->query
                                ." and these parameters:".json_encode($params)
                            );

            }

		    return $results;
        }



    }


?>