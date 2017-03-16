<?php namespace HelixTech\asstAPI\Models;

use HelixTech\asstAPI\{Connection, Query, Output};
use HelixTech\asstAPI\Exceptions\UnableToAuthenticateUserCredentials;

class Analytics{

    public static function display(){
       try{
           if (Connection::authenticate('AdminTable')){

           // build the data retrival queries
               $query = new Query(SELECT, "COUNT(DISTINCT CXTN_IP) FROM ConnectionLog");
               $numDistinctIP = $query->execute();

               $query = new Query(SELECT, "COUNT(DISTINCT CXTN_USER) FROM ConnectionLog");
               $numDistinctUsers = $query->execute();

               $timePeriod = time() + (7 * 24 * 60 * 60);
               $query = new Query(SELECT, "COUNT(*) FROM ConnectionLog WHERE UNIX_TIMESTAMP(CXTN_TIME) > $timePeriod");
               $numAPIRequestsINlastWeekPerDay = ($query->execute())/7;

               $query = new Query(SELECT, "* FROM ConnectionLog WHERE CXTN_ERRORS IS NOT NULL ORDER BY CXTN_USER, CXTN_IP");
               $CnxtsByIP = $query->execute();



            // combine the quries and output as JSON via Output class
               $analyticResults = array(
                                    "DISTINCT_IP_COUNT" => $numDistinctIP,  
                                    "DISTINCT_USER_COUNT" => $numDistinctUsers, 
                                    "AVERAGE_REQUESTS" => $numAPIRequestsINlastWeekPerDay,
                                    "Data" => $CnxtsByIP);
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