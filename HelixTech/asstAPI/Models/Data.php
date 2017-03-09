<?php namespace HelixTech\asstAPI\Models;
/**
 * @author Philip Alton
 * @copyright Helix Tech Ltd. 2017
 * @file Data.php
 * @package asstAPI
 *
 * @todo write sync methods
 */

use HelixTech\asstAPI\{Output, Connection, Query};
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

    public static function syncData($method, $data){
        try{
            if (Connection::authenticate()){

                    switch ($method) {
                        case 'POST':
                            // call method to do something syncingness.
                            Output::setOutput(Data::syncAllData($data));
                            break;

				        case 'PUT':
					        // call method to push a single data set
                            Output::setOutput(Data::pushData($data['date'], $data));
					        break;

				        case 'GET':
					        // call method to get Data
					        Output::setOutput(Data::pullData($data['date']));
					        break;

				        default:
                            Output::errorMsg("HTML verb has no corisponding API action");
				        // throw exception

			        }

            } else {
                Output::setOutput('Invalid Username/Password Combination');
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
    public static function pushData($date, $data){
        // check data does not already exist
            // if so then terminate
            // throw data conflict error_get_last
        
        // else run add data item to table(s)
    }


    /**
     * Summary of pullData: request a single data item from the server database
     * @param mixed $data
     * @return array $results
     */
    public static function pullData($date){
        $results = Array();
        // SQL query to return $data against date for User::username


        return $results;
    }



    /**
     * Summary of syncAllData:
     */
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
                    // data not consistent - needs to be fixed

                }        
            }
        }


    }



    /**
     * Summary of checkDataConsistency:
     * @param mixed $count
     */
    public static function checkDataConsistency($table, $columnName, $count){
        $isConsistent;    
        
        $query = New Query(SELET, "$columnName FROM $table WHERE UniqueID = :uID");
        $countAPI = $query->execute([':uID' => User::$uID]);

            if (floatval($count) === floatval($countAPI)){
                $isConsistent = true;
            } else {
                $isConsistent = false;
            }

        return $isConsistent;
    }














    /**
     * Summary of syncLastThree: check the last three records and sync where needed
     */
    public static function syncLastThree(){


    }

    /**
     * Summary of snycAllData: return
     */
    public static function pullAllData(){


    }


}
?>