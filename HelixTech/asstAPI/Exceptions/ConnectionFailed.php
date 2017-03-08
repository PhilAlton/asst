<?php namespace HelixTech\asstAPI\Exceptions;

      use HelixTech\asstAPI\{Query, Connection};

      /**
       * Summary of ConnectionFailed - log failures to connect to the API
       */
      class ConnectionFailed extends AbstractLoggedException
      {
          /**
           * Summary of ConnectionFailed
           *
           */
          public static function logError(){
            AbstractLoggedException::$dbMessage .= "Connection Failed; ";
            $query = New Query(UPDATE, "ConnectionLog ".
                    "SET CXTN_ERRORS=:msg ".
                    "WHERE `CXTN_ID` =:cID");
            $query->silentExecute([':cID' => Connection::getCID(), ':msg' => AbstractLoggedException::$dbMessage]);

            }

      }

?>