<?php
	require_once "../asst/output.php";
	require '../asst/database.php';
	
	$database = New Database;

        $query = 'CREATE TABLE table1(Four int, Nine int)';
 /*       $database->query($query);

       
        if($database->execute()){
            Output::setOutput( "yay";
        }else{
            Output::setOutput( "didn't work";
        }

*/
//        $query = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "UserTable"';
Output::setOutput("dbtest5");
        $query = 'SELECT * FROM `UserTable` WHERE `ID` =:id';
        $id = '00001';
    
        $database->query($query);
		
		Output::setOutput("qry excute");
		
        var_dump($database->resultset([':id' => $id]));
            Output::setOutput("yay");
      //  else{
       //     Output::setOutput("didn't work");
       // }
Output::setOutput("end");

?>