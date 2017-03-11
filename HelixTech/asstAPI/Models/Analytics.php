<?php namespace HelixTech\asstAPI\Models;

use HelixTech\asstAPI\{Connection, Query};

class Analytics{

    public static function display(){
       try{
           if (Connection::authenticate('AdminTable')){

               $query = new Query(SELECT, "COUNT(DISTINCT CXTN_ID) FROM ConnectionLog");
               $numDistinctIP = $query->execute();

               $query = new Query(SELECT, "* FROM ConnectionLog GROUP BY CXTN_ID");
   //            $CnxtsByIP = $query->execute();

               var_dump($numDistinctIP);
   //            var_dump($CnxtsByIP);


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