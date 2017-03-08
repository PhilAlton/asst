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
            /*
            $query = new Query(SELECT, "CXTN_ERRORS FROM ConnectionLog "
                                    ."WHERE CXTN_ID =:cID"
                            );
            $message = $query->silentExecute([':cID' => Connection::getCID()]);

            $message = $message."Connection Failed; ";
            $query = New Query(UPDATE, "ConnectionLog ".
                    "SET CXTN_ERRORS=:msg ".
                    "WHERE `CXTN_ID` =:cID");
            $query->silentExecute([':cID' => Connection::getCID(), ':msg' => $message]);
         */
        }


      }

?>