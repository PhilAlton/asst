<?php namespace HelixTech\asstAPI\Exceptions;

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
    public function logError(){
        AbstractLoggedException::$dbMessage .= "Insecure Connection; ";
        parent::log();
    }


}

?>