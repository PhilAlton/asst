<?php

require '../asst/database.php';
	
	
// Syntax: 
// Query query = New Query($query);
// $query->execute([    ':id' => '00001',
//                      ':otherParam' => $oP
//                  ]);



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
        foreach ($params as $param => $value){
            $this->database->bind($param, $value);
        }
        $this->database->resultset($params);

    }




 //   $query = 'SELECT * FROM `UserTable` WHERE `ID` =:id';
 //   $this->database->query($query);                   // prep the query, need to bind parameters	
 //   $id = '00001';                                    // $id not set here
 //   $this->database->resultset([':id' => $id]);       //$database->resultset($this->params);





}


?>