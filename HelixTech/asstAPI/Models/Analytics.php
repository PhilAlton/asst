<?php namespace HelixTech\asstAPI\Models;

use HelixTech\asstAPI\{Connection, Query};
use HelixTech\asstAPI\Exceptions\UnableToAuthenticateUserCredentials;

class Analytics{

    public static function display(){
       try{
           if (Connection::authenticate('AdminTable')){

               $query = new Query(SELECT, "COUNT(DISTINCT CXTN_IP) FROM ConnectionLog");
               $numDistinctIP = $query->execute();

               $query = new Query(SELECT, "COUNT(DISTINCT CXTN_USER) FROM ConnectionLog");
               $numDistinctUsers = $query->execute();

               $query = new Query(SELECT, "* FROM ConnectionLog ORDER BY CXTN_USER, CXTN_IP");
               $CnxtsByIP = $query->execute();

               $analyticResults = array("DISTINCT_IP_COUNT" => $numDistinctIP,  "DISTINCT_USER_COUNT" => $numDistinctUsers, "Data", $CnxtsByIP);



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