<?php namespace HelixTech\asstAPI\Exceptions;

      /**
       * Summary of ConnectionFailed - log failures to connect to the API
       */
      class ConnectionFailed extends AbstractLoggedException
      {
          /**
           * Summary of ConnectionFailed
           * @todo connect to the database to store log info for failed attempts to connect to the API
           *
           */
          public static function logError($message){
              $message = $message."...   ";
              $query = New Query(UPDATE, "ConnectionLog ".
                     "SET CXTN_ERRORS=:msg".
                     "WHERE `CXTN_ID` =:cID");
              $query->execute([':cID' => Connection::$cID, ':msg' => $message]);
        }


      }

?>