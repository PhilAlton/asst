<?php namespace HelixTech\asstAPI\Models;

use HelixTech\asstAPI\{Connection, Query, Output};
use HelixTech\asstAPI\Exceptions\UnableToAuthenticateUserCredentials;

class Research{

    public static function display(){
       try{
           if (Connection::authenticate('AdminTable')){

           // build the data retrival queries
		    $results = array();
		    //$query = New Query(SELECT, '* FROM `ResearchTable` WHERE `Research_Participant` =:Research_Participant');
		    //$results = array_merge( $results, $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':Research_Participant' => 1]));
            $query = New Query(SELECT, '* FROM `UserTable` WHERE `Research_Participant` =:Research_Participant');
		    $results = array_merge( $results, $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':Research_Participant' => 1]));

			$researchParticipants = $results;

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