<?php

require 'database.php';


class Query {

    private $query;
    // private $params;
    private $database;

    public function __construct($query){
        $this->database = New Database;
        $this->query = $query;
    }


    public function execute($params){
        $this->database->query($this->query);
        foreach ($params as $param => $value){				// Pass parameters to PDO statement
            $this->database->bind(
		//		encrypt
				($param),							// Encrypt all parameters here: erncrypt($param)
				$value
			);
        }

		$results = $this->database->resultset();

		// reduce output in case of single row, or single result
		if (count($results) == 1)
		{
			foreach ($results as $result){$results = $result;}
			if (count($results) == 1){foreach ($results as $result){$results = $result;}}
		}


		// algorithm to decrypt all database output
	//	array_walk_recursive($results, function(&$value, $key){$value = decrypt($value);});

		return $results;
    }



}


?>