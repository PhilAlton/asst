<?php

require 'database.php';
define("INSERT", "INSERT");
define("UPDATE", "UPDATE");
define("SELECT", "SELECT");
define("CREATE", "CREATE");
define("DELETE", "DELETE");
define("DROP", "DROP");
define("EXISTS", "IF EXISTS (SELECT");




class Query {

    private $query;
    private $database;
	private $queryType;

    public function __construct($queryType, $query){
        $this->database = New Database;
		$this->queryType = $queryType;
        $this->query = $queryType." ".$query;
    }


    public function execute($params = null){
        $results;
		$this->database->query($this->query);
		if (isset($params)){
			foreach ($params as $param => $value){				// Pass parameters to PDO statement
				$this->database->bind(
			//		encrypt
					$param,							// Encrypt all parameters here: erncrypt($param)
					$value
				);
			}
		}

        $results = false;

        try {
            $results = $this->database->execute();

        } catch (Exception $e) {
            http_response_code(406);
            Output::errorMsg("caught exception: ".$e->getMessage()
                                ." - with SQL Statement ".$this->query
                                ." and these parameters:".json_encode($params)
                            );
        }


		switch ($this->queryType)
		{
            case EXISTS:
			case SELECT:
				$results = $this->database->resultset();

				// reduce output in case of single row, or single result
				if (count($results) == 1)
				{
					foreach ($results as $result){$results = $result;}
					if (count($results) == 1){foreach ($results as $result){$results = $result;}}
				}

				// algorithm to decrypt all database output
			    // array_walk_recursive($results, function(&$value, $key){$value = decrypt($value);});
				break;

			case INSERT:
				http_response_code(201); // content created
				break;

			case UPDATE:
				http_response_code(204); // No content *(request fulfilled)
				break;

			case DELETE:
				http_response_code(204); // No content *(request fulfilled)
				break;

			case DROP:
				http_response_code(204); // No content *(request fulfilled)
				break;

			default:
				http_response_code(200);
		}

		return $results;
    }



}


?>