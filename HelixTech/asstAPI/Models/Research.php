<?php namespace HelixTech\asstAPI\Models;

use HelixTech\asstAPI\{Connection, Query, Output};
use HelixTech\asstAPI\Exceptions\UnableToAuthenticateUserCredentials;

class Research{

    public static function display(){
       try{
           if (Connection::authenticate('AdminTable')){

           // build the data retrival queries
			$query = new Query(SELECT, "COUNT(DISTINCT UniqueID) FROM ResearchTable");
			$numDistinctUsers = $query->execute(SIMPLIFY_QUERY_RESULTS_ON);

            $query = New Query(SELECT,
							"Age, Gender, Age_Of_Symptom_Onset, Firstname, Surname, DoB, Hospital from UserTable"
							." RIGHT JOIN ResearchTable ON UserTable.UniqueID = ResearchTable.UniqueID"
							." WHERE UserTable.Research_Participant = 1"      
						);
            $researchParticipants = $query->execute(SIMPLIFY_QUERY_RESULTS_ON);



            // combine the quries and output as JSON via Output class
            $analyticResults = array( 
                            "DISTINCT_USER_COUNT" => $numDistinctUsers, 
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