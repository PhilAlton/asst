<?php namespace HelixTech\asstAPI\Models;

use HelixTech\asstAPI\{Connection, Query, Output};
use HelixTech\asstAPI\Exceptions\UnableToAuthenticateUserCredentials;

class Home{

    public static function display(){
		try{
			if (Connection::authenticate('AdminTable')){

			// Execute any Admin queries here.
			        //  $query = new Query(SELECT, "COUNT(DISTINCT UniqueID) FROM ResearchTable");
			        //  $numDistinctUsers = $query->execute(SIMPLIFY_QUERY_RESULTS_ON);

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