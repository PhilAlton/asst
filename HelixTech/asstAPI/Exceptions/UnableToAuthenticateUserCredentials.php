<?php namespace HelixTech\asstAPI\Exceptions;

/**
 * Logged Exception: Unable to Authenticate
 */
class UnableToAuthenticateUserCredentials extends AbstractLoggedException
{

    /**
     * Summary of logError - log failures to authenticate user details
     */
    public static function logError(){
        AbstractLoggedException::$dbMessage .= "Failed authentication; ";
        parent::log();
    }


}

?>