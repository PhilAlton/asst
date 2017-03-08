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
          public static function logError($message){
              $message = $message."...   ";
              $query = New Query(UPDATE, "ConnectionLog ".
                     "SET CXTN_ERRORS=:msg ".
                     "WHERE `CXTN_ID` =:cID");
              $query->execute([':cID' => Connection::getCID(), ':msg' => $message]);
        }


      }

?>