<?php namespace HelixTech\asstAPI\Exceptions;

     use HelixTech\asstAPI\{Query, Connection};
    /**
    * Logged Exception: Attempted Access to Invalid URI
    */
    class InvalidURI extends AbstractLoggedException
    {

        /**
        * Log attempts to access invalid URLs
        * This might indicate either that a developer is consuming the API poorly,
        * Or that an attacker is attempting to explore vulnerabilities
        *
        * @todo write function to log invalid URI expcetions
        *
        *
        */
        public static function logError($message){
            $message = $message."...   ";
            $query = New Query(UPDATE, "ConnectionLog ".
                   "SET CXTN_ERRORS=:msg".
                   "WHERE `CXTN_ID` =:cID");
            $query->execute([':cID' => Connection::getCID(), ':msg' => $message]);
        }


    }

?>
