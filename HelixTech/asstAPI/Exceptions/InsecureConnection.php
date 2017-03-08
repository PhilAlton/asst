<?php namespace HelixTech\asstAPI\Exceptions;

use HelixTech\asstAPI\{Query, Connection};
/**
* Summary of InsecureConnection - log failures to connect to the API via HTTPS
*/
class InsecureConnection extends AbstractLoggedException
{
    /**
    * Summary of logError
    * @todo connect to the database to store log info for attempts to connect via HTTP
    * THis may indicate that a developer has not written their app consuming this API securely
    */
    public static function logError(){
        $query = new Query(SELECT, "CXTN_ERRORS FROM ConnectionLog "
                                    ."WHERE CXTN_ID =:cID"
                            );
        $message = $query->silentExecute([':cID' => Connection::getCID()]);

        $message = $message."Insecure Connection; ";
        $query = New Query(UPDATE, "ConnectionLog ".
                "SET CXTN_ERRORS=:msg ".
                "WHERE `CXTN_ID` =:cID");
        $query->silentExecute([':cID' => Connection::getCID(), ':msg' => $message]);
}


      }

?>