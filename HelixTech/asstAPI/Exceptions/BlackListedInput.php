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
           * the system administrator
           */
        public static function logError($message){
            $message = $message."...   ";
            $query = New Query(UPDATE, "ConnectionLog ".
                   "SET `CXTN_ERRORS`=:msg ".
                   "WHERE `CXTN_ID` =:cID");
            $query->execute([':cID' => Connection::getCID(), ':msg' => $message]);



            // ALert system admin
            // E.g Via slack

            // Instantiate with defaults, so all messages created
            // will be sent from 'Cyril' and to the #accounting channel
            // by default. Any names like @regan or #channel will also be linked.
            $settings = [
                'username' => 'asstapi',
                'channel' => '#asstapi-log',
                'link_names' => true
            ];

            $client = new Maknz\Slack\Client('https://hooks.slack.com/services/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', $settings);

            $client->send('BlackListed word detected in HTTP input');



        }


      }

?>