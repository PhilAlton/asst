<?php
	require '../asst/database.php';
	
	$database = New Database;

        $query = 'CREATE TABLE table1(Four int, Nine int)';
 /*       $database->query($query);

       
        if($database->execute()){
            echo "yay";
        }else{
            echo "didn't work";
        }

*/
//        $query = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "UserTable"';
echo "dbtest5";
        $query = 'SELECT * FROM `UserTable` WHERE `ID` =:id';
        $id = '00001';
    
        $database->query($query);
		
		echo "qry excute";
		
        var_dump($database->resultset([':id' => $id]));
            echo ("yay");
      //  else{
       //     echo "didn't work";
       // }
echo "end";

?>