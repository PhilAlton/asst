<?php namespace HelixTech\asstAPI\Models;

use HelixTech\asstAPI\{Connection, Query, Output};
use HelixTech\asstAPI\Exceptions\UnableToAuthenticateUserCredentials;

class Analytics{

    public static function display(){
       try{
           if (Connection::authenticate('AdminTable')){

           // build the data retrival queries
               $query = new Query(SELECT, "COUNT(DISTINCT CXTN_IP) FROM ConnectionLog");
               $numDistinctIP = $query->execute(SIMPLIFY_QUERY_RESULTS_ON);

               $query = new Query(SELECT, "COUNT(DISTINCT CXTN_USER) FROM ConnectionLog");
               $numDistinctUsers = $query->execute(SIMPLIFY_QUERY_RESULTS_ON);

               $timePeriod = time() - (7 * 24 * 60 * 60); //1 week
               $query = new Query(SELECT, "COUNT(*) FROM ConnectionLog WHERE UNIX_TIMESTAMP(CXTN_TIME) > $timePeriod");
               $numAPIRequestsINlastWeekPerDay = ($query->execute(SIMPLIFY_QUERY_RESULTS_ON))/7;

               $query = new Query(SELECT, "* FROM ConnectionLog" 
                                            ." WHERE CXTN_ERRORS IS NOT NULL" 
                                            ." ORDER BY CXTN_IP, CXTN_USER"
                                    );
               $CnxtsByIP = $query->execute(SIMPLIFY_QUERY_RESULTS_ON);



            // combine the quries and output as JSON via Output class
               $analyticResults = array(
                                    "DISTINCT_IP_COUNT" => $numDistinctIP,  
                                    "DISTINCT_USER_COUNT" => $numDistinctUsers, 
                                    "AVERAGE_REQUESTS" => $numAPIRequestsINlastWeekPerDay,
                                    "Data" => $CnxtsByIP);

               include $_Server['DOCUMENT_ROOT/asst/HelixTech/Public/asstAPI/analytics.php'].'.php';
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