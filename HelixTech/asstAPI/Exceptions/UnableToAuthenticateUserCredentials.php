<?php namespace HelixTech\asstAPI\Exceptions;

use HelixTech\asstAPI\{Query, Connection};
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
        AbstractLoggedException::$dbMessage .= "Failed authentication; ";
        $query = New Query(UPDATE, "ConnectionLog ".
               "SET CXTN_ERRORS=:msg ".
               "WHERE `CXTN_ID` =:cID");
        $query->silentExecute([':cID' => Connection::getCID(), ':msg' => AbstractLoggedException::$dbMessage]);
    }




}


?>