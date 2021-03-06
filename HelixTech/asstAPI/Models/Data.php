<?php namespace HelixTech\asstAPI\Models;
/**
 * @author Philip Alton
 * @copyright Helix Tech Ltd. 2017
 * @file Data.php
 * @package asstAPI
 *
 * @todo write sync methods
 */

use HelixTech\asstAPI\{Output, Connection, Query, Paginate};
use HelixTech\asstAPI\Models\{User};
use HelixTech\asstAPI\Exceptions\{UnableToAuthenticateUserCredentials};


/**
 * Summary of Data
 */
class Data {

/**
 * @todo write method to recieve last 3 items and compare
 *
 * @todo write method to count dates as unique integers
 *
 * @todo write method to send and compare whole data list
 *
 *
 *
 *
 */

    private static $userTableArray = Array();



    public static function syncData($method, $data){
        try{
            if (Connection::authenticate()){

                // Ensure correct tables are looked at.
                array_push(Data::$userTableArray, 'GEN_DATA_TABLE_');
                $query = New Query(SELECT, 'Research_Participant FROM `UserTable` WHERE `UniqueID` =:UniqueID');
                $isRchParticipant = $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UniqueID' => User::$uID]);
                if ($isRchParticipant){array_push(Data::$userTableArray, 'RCH_DATA_TABLE_');}


                    switch ($method) {
                        case 'POST':
                            // call method to do something syncingness.
                            Output::setOutput(Data::pushData($data));       // add a single data set to the table
                            break;

				        case 'GET':
					        // call method to get Data
					        Output::setOutput(Data::pullData($data['lastUpdate']));       //$data['remoteLastUpdate'] - last time remote client was sync'd with the database
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
        } catch (\OutOfBoundsException $e){
            Output::errorMsg("Full Data Set Not Supplied.");

        } catch (UnableToAuthenticateUserCredentials $e){
            http_response_code(401);
            Output::errorMsg("Unable to authenticate: ".$e->getMessage().".");
        }

    }


    /**
     * Summary of pushData: send a single data item to the server database
     * @param mixed $Data
     */
    public static function pushData($data){
        // check data does not already exist
        $results = array();

        foreach (Data::$userTableArray as $userTable){

            // Handle any conflicts
            $query = New Query(SELECT, "Date from $userTable".User::$uID." WHERE date = :date");
            $conflict = $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':date' => $data['Date']]);

            if (count($conflict) !== 0){
                $results = array_merge($results, Array($userTable.User::$uID => "database conflict, data-set {$data['Date']} in  $userTable".User::$uID." alraedy exists"));
		    } else {
            // If no conflicts then proceed:

                // generate array of possible columns
                $query = New Query(SELECT, "COLUMN_NAME "
                                   ."FROM INFORMATION_SCHEMA.COLUMNS "
                                   ."WHERE TABLE_NAME=:tableName"
                                   );
                $columns = $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':tableName' => ($userTable.User::$uID)]);

                // Unify coluns and values
                $values = Array(); $columnNames = Array();
                foreach ($columns as $column){
                    $columnNames[] = $column['COLUMN_NAME'];
                    if (!isset($data[$column['COLUMN_NAME']])){
                        unset($columnNames[array_search($column['COLUMN_NAME'], $columnNames)]);
                    } else {
                        $values[] = $data[$column['COLUMN_NAME']];
                    }
                } 

                if (count($columnNames)===1){
                    //dont run the query
                    unset($columnNames[array_search('Date', $columnNames)]);
                } else {
                    // stringify columns and values
                    $columnString = implode(", ", $columnNames);
                    $boundColumns = ":".implode(", :", $columnNames);
                    $boundValues = array_combine(explode(", ", $boundColumns) , $values);

                    // create and execute query to insert data-set
                    $query = New Query(INSERT, "INTO $userTable".User::$uID."(".$columnString.") VALUES (".$boundColumns.")");
                    $results = array_merge($results, $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  $boundValues));
                } 

            }

        }


        /** @todo in V2: update count var with new data-as-integer */

        //output results
        return $results;


    }


    /**
     * Summary of pullData: request a single data item from the server database
     * @param mixed $data
     * @return array $results
     */
    public static function pullData($remoteLastUpdate, $paginationLimit = 20){

        $results = Array();

        // SQL query to return $data against date for User::uID
        $i = 0;
        $join = "";
        
		/*
        foreach (Data::$userTableArray as $userTable){    
            $i++;     
            if ($i == 1){
                $firstTable = $userTable.User::$uID;
                $tables = $firstTable;
                $whereClause = " WHERE (UNIX_TIMESTAMP(".$firstTable.".LastUpdate) > :remoteLastUpdate OR ".$firstTable."LastUpdate IS NULL)";
            } else {
                $nextTable = $userTable.User::$uID;
                $tables = $tables.", ".$nextTable;
                $rightJoin = $rightJoin." RIGHT JOIN ".$nextTable." ON $firstTable.Date = $nextTable.Date";
                $leftJoin = $leftJoin." LEFT JOIN ".$nextTable." ON $firstTable.Date = $nextTable.Date";
                $whereClause = $whereClause." AND (UNIX_TIMESTAMP(".$nextTable.".LastUpdate) > :remoteLastUpdate OR ".$nextTable."LastUpdate IS NULL)";
                $endWhere = " AND $firstTable.Date IS NULL";
            }
        }
        */
		
     //   $query = New Query(SELECT, "" 
     //                   ."* FROM $firstTable"
     //                               .$leftJoin
     //                               .$whereClause
     //                               ." ORDER BY $firstTable.Date"
     //                   ." UNION"
     //                   ." SELECT" 
     //                   ." * FROM $firstTable"
     //                               .$rightJoin
     //                               .$whereClause
                  //                  .$endWhere
                               //     ." ORDER BY $firstTable.Date"
     //   );



/*
        $query1 = new Query(SELECT, 
            "* from GEN_DATA_TABLE_100 "
            ."INNER JOIN RCH_DATA_TABLE_100 ON GEN_DATA_TABLE_100.Date = RCH_DATA_TABLE_100.Date "
            ."WHERE UNIX_TIMESTAMP(GEN_DATA_TABLE_100.LastUpdate) > :remoteLastUpdate "
            ."OR UNIX_TIMESTAMP(RCH_DATA_TABLE_100.LastUpdate) > :remoteLastUpdate "
        );
*/

		if (count(Data::$userTableArray) == 1){
		
			$genTable = "GEN_DATA_TABLE_".User::$uID;

			$query1 = New Query(SELECT,
				"* from $genTable"
				." WHERE UNIX_TIMESTAMP($genTable.LastUpdate) > :remoteLastUpdate"       
			);

			$results = array_merge($results, $query1->execute(SIMPLIFY_QUERY_RESULTS_OFF,  [':remoteLastUpdate' => $remoteLastUpdate]));

		} else {
		
			$genTable = "GEN_DATA_TABLE_".User::$uID;
			$rchTable = "RCH_DATA_TABLE_".User::$uID;

			$query1 = New Query(SELECT,
				"* from $genTable"
				." RIGHT JOIN $rchTable ON $genTable.Date = $rchTable.Date"
				." WHERE UNIX_TIMESTAMP($genTable.LastUpdate) > :remoteLastUpdate"
				." OR UNIX_TIMESTAMP($rchTable.LastUpdate) > :remoteLastUpdate"          
			);

		
			$query2 = New Query(SELECT,
				"* from $rchTable"
				." RIGHT JOIN $genTable ON $rchTable.Date = $genTable.Date"
				." WHERE UNIX_TIMESTAMP($genTable.LastUpdate) > :remoteLastUpdate"
				." AND $rchTable.Date IS NULL"
			);


			$results = array_merge($results, $query1->execute(SIMPLIFY_QUERY_RESULTS_OFF,  [':remoteLastUpdate' => $remoteLastUpdate]));
			$results = array_merge($results, $query2->execute(SIMPLIFY_QUERY_RESULTS_OFF,  [':remoteLastUpdate' => $remoteLastUpdate]));

		}


    
// need to amend $results adding to account for single returns
//      from both query 1 and query 2; else return structure becomes jagged array
//  Also need to create queries dynamically....
    
        if (count($results) > $paginationLimit){
            $results = Paginate::create($results, $paginationLimit);
        }
        return $results;
    }



// Code to test for database consistency
//     May be incorporated into a subsequent version of the API
    /**
     * Summary of syncAllData:


    public static function syncAllData($data){
        // method to get user data against timestamp and either update (call postData),
        //	or withdraw (call pullData) any additional server data and pass back to user

        $results = Array();

        $countArray = Array('ResearchTable' => 'Rch_Data_Count', 'UserTable' => 'Gen_Data_count');
        foreach ($countArray as $table => $countColumn){
            if (isset($data[$countColumn])){

                $isConsistent = checkDataConsistency($table, $countColumn, $data[$countColumn]);

                if ($isConsistent){
                    $results = array_push($results, Array($countColumn => $isConsistent));

                } else {
                    // data not consistent - needs to be updated
                    // send back a list of all dates for that table
                    // client can then compare
                    foreach ($this->userTableArray as $userTable){
                        $query = New Query(SELECT, "Date from $userTable".User::$uID);
                        $results = array_push($results, array($userTable => $query->execute(1)));
                    }


                }
            }
        }


    }


*/
    /**
     * Summary of checkDataConsistency:
     * @param mixed $count
     *

    public static function checkDataConsistency($table, $columnName, $count){
        $isConsistent;

        $query = New Query(SELET, "$columnName FROM $table WHERE UniqueID = :uID");
        $countAPI = $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':uID' => User::$uID]);

            if (floatval($count) === floatval($countAPI)){
                $isConsistent = true;
            } else {
                $isConsistent = false;
            }

        return $isConsistent;
    }
*/

}
?>