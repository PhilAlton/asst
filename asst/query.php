<?php

require 'database.php';
define("INSERT", "INSERT");
define("UPDATE", "UPDATE");
define("SELECT", "SELECT");
define("CREATE", "CREATE");
define("DELETE", "CREATE");




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
        foreach ($params as $param => $value){				// Pass parameters to PDO statement
            $this->database->bind(
		//		encrypt
				$param,							// Encrypt all parameters here: erncrypt($param)
				$value
			);
        }


		switch ($this->queryType)
		{
			case SELECT:
				$results = $this->database->resultset();

				// reduce output in case of single row, or single result
				if (count($results) == 1)
				{
					foreach ($results as $result){$results = $result;}
					if (count($results) == 1){foreach ($results as $result){$results = $result;}}
				}

				// algorithm to decrypt all database output
			//	array_walk_recursive($results, function(&$value, $key){$value = decrypt($value);});
				break;

			case INSERT:
				$this->database->execute();
				http_response_code(201); // created
				$results = true;
				break;

			case UPDATE:
				$this->database->execute();
				http_response_code(204); // No content *(request fulfilled)
				$results = true;
				break;

			case DELETE:
				$this->database->execute();
				http_response_code(204); // No content *(request fulfilled)
				$results = true;
				break;


			default:
				$this->database->execute();
		}

		return $results;
    }



}


?>