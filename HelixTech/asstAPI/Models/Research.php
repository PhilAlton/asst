<?php namespace HelixTech\asstAPI\Models;

use HelixTech\asstAPI\{Connection, Query, Output};
use HelixTech\asstAPI\Exceptions\UnableToAuthenticateUserCredentials;

class Research{

    public static function display(){
       try{
           if (Connection::authenticate('AdminTable')){

           // build the data retrival queries
               $query = New Query(SELECT,
									"* from UserTable"
									." RIGHT JOIN ResearchTable ON UserTable.UniqueID = ResearchTable.UniqueID"
									." WHERE UserTable.Research_Participant = 1"      
								);
               $researchParticipants = $query->execute(SIMPLIFY_QUERY_RESULTS_ON);



            // combine the quries and output as JSON via Output class
               $analyticResults = array(
                                    //"DISTINCT_IP_COUNT" => $numDistinctIP,  
                                    //"DISTINCT_USER_COUNT" => $numDistinctUsers, 
                                    //"AVERAGE_REQUESTS" => $numAPIRequestsINlastWeekPerDay,
                                    "Data" => $researchParticipants);

			
               Output::setOutput($researchParticipants);



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