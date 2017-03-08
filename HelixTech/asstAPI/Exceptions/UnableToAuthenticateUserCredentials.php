<?php namespace HelixTech\asstAPI\Exceptions;

use HelixTech\asstAPI\{Query, Connection);
/**
 * Logged Exception: Unable to Authenticate
 * @todo error logging
 */
class UnableToAuthenticateUserCredentials extends AbstractLoggedException
{

    /**
     * Summary of logError - log failures to authenticate user details
     * @todo connect to the database to store log info
     */
    public static function logError(){
        $query = new Query(SELECT, "CXTN_ERRORS FROM ConnectionLog "
                                ."WHERE CXTN_ID =:cID"
                            );
        $message = $query->silentExecute([':cID' => Connection::getCID()]);

        $message = $message."Failed authentication; ";
        $query = New Query(UPDATE, "ConnectionLog ".
               "SET CXTN_ERRORS=:msg ".
               "WHERE `CXTN_ID` =:cID");
        $query->silentExecute([':cID' => Connection::getCID(), ':msg' => $message]);
    }




}


?>