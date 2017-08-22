<?php namespace HelixTech\asstAPI\Exceptions;

/**
* Summary of UsernameNotAValidEmailAddress - log failures to connect to the API with an email address
*/
class UsernameNotAValidEmailAddress extends AbstractLoggedException
{
    /**
    * Summary of logError
    * @todo connect to the database to store log info for attempts to connect via HTTP
    * THis may indicate that a developer has not written their app consuming this API securely
    */
    public function logError(){
        AbstractLoggedException::$dbMessage .= "UserName is not an email address; ";
        parent::log();
    }


}

?>