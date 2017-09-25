<?php namespace HelixTech\asstAPI\Models;

use HelixTech\asstAPI\{Connection, Query, Output};
use HelixTech\asstAPI\Exceptions\UnableToAuthenticateUserCredentials;

class Research{

    public static function display(){
       try{
           if (Connection::authenticate('AdminTable')){

           // build the data retrival queries

		   $query = new Query(SELECT, "COUNT(DISTINCT CXTN_IP) FROM ConnectionLog");
           $numDistinctIP = $query->execute(SIMPLIFY_QUERY_RESULTS_ON);


		//    $results = array();
		//    $query = New Query(SELECT, '* FROM `ResearchTable` WHERE `UniqueID` =:UniqueID');
		//    $results = array_merge( $results, $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UniqueID' => 217]));
        //    $query = New Query(SELECT, '* FROM `UserTable` WHERE `UniqueID` =:UniqueID');
		//    $results = array_merge( $results, $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UniqueID' => 217]));


			$researchParticipants = $numDistinctIP;

            // combine the quries and output as JSON via Output class
               $analyticResults = array(
                                    //"DISTINCT_IP_COUNT" => $numDistinctIP,  
                                    //"DISTINCT_USER_COUNT" => $numDistinctUsers, 
                                    //"AVERAGE_REQUESTS" => $numAPIRequestsINlastWeekPerDay,
                                    "Data" => $researchParticipants);

               Output::setOutput($analyticResults);



            } else {
                Output::setOutput('Invalid Username/Password Combination');
                $e = "Failed to validate UserName against Password";
                throw new UnableToAuthenticateUserCredentials($e);
            }
        } catch (UnableToAuthenticateUserCredentials $e){
            http_response_code(401);
            Output::errorMsg("Unable to authenticate: ".$e->getMessage().".");
        }

    }




}


?>