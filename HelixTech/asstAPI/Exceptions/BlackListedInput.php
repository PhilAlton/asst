<?php namespace HelixTech\asstAPI\Exceptions;

      use HelixTech\asstAPI\{Query, Connection};


      /**
       * Summary of BlackListedInput - log attempts to send blacklisted input to the server
       * @todo system admin notification
       */
      class BlackListedInput extends AbstractLoggedException
      {
          /**
           * Summary of BlackListedInput
           * @todo connect to the database to store log info for attempts to send blacklisted input to the server
           * This is a good indication of a attempted hack, and therefore a specific notification will be sent to
           * the system administrator, via slack.
           */
        public static function logError(){
            $query = new Query(SELECT, "CXTN_ERRORS FROM ConnectionLog "
                                        ."WHERE CXTN_ID =:cID"
                                );
            $message = $query->silentExecute([':cID' => Connection::getCID()]);

            $message = $message."BlackList Attempt; ";
            $query = New Query(UPDATE, "ConnectionLog ".
                   "SET CXTN_ERRORS=:msg ".
                   "WHERE `CXTN_ID` =:cID");
            $query->silentExecute([':cID' => Connection::getCID(), ':msg' => $message]);


            // ALert system admin via slack
            parent::callSLack('BlackListed word detected in HTTP input');
        }


      }

?>