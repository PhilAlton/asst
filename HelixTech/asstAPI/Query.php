<?php namespace HelixTech\asstAPI;

    use HelixTech\asstAPI\{Database, Crypt};

    //require 'database.php';
    define("INSERT", "INSERT");
    define("UPDATE", "UPDATE");
    define("SELECT", "SELECT");
    define("CREATE", "CREATE");
    define("DELETE", "DELETE");
    define("DROP", "DROP");




    class Query {

        private $query;
        private $database;
	    private $queryType;

        public function lastInsertId(){
            return $this->database->lastInsertId();
        }

        public function __construct($queryType, $query){
            $this->database = New Database;
		    $this->queryType = $queryType;
            $this->query = $queryType." ".$query;
        }



        public function silentExecute($params = null){
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



        public function buildQuery($params = null){

            $this->database->query($this->query);
		    if (isset($params)){
			    foreach ($params as $param => $value){				// Pass parameters to PDO statement
				    $this->database->bind(
			    //		Crypt::encrypt
					    $param,							// Encrypt all parameters here: uncomment and add () to $param
					    $value
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
        public function execute($params = null){

            $this->buildQuery($params);
            $results = false;

            try {
                $this->database->execute();
                $results = $params;

                switch ($this->queryType){
			        case SELECT:
				        $results = $this->database->resultset();

				        // reduce output in case of single row, or single result
				        if (count($results) == 1)
				        {
					        foreach ($results as $result){$results = $result;}
					        if (count($results) == 1){foreach ($results as $result){$results = $result;}}
				        }

				        // algorithm to decrypt all database output
                        // array_walk_recursive($results, function(&$value, $key){$value = Crypt::decrypt($value);});
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
				        http_response_code(204); // No content *(request fulfilled)
				        break;

			        case DELETE:
				        http_response_code(200); // No content *(request fulfilled)
				        break;

			        case DROP:
				        http_response_code(204); // No content *(request fulfilled)
				        break;

			        default:
				        http_response_code(200);
		        }

            }
            catch (Exception $e) {
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