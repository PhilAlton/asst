<?php namespace HelixTech\asstAPI\Exceptions;

      /**
       * Summary of BlackListedInput - log attempts to send blacklisted input to the server
       */
      class BlackListedInput extends AbstractLoggedException
      {
          /**
           * Summary of BlackListedInput
           * This is a good indication of a attempted hack, and therefore a specific notification will be sent to
           * the system administrator, via slack.
           */
          public function logError(){
            AbstractLoggedException::$dbMessage .= "BlackList Attempt; ";
            parent::log();
    
            // ALert system admin via slack
            parent::callSlack('BlackListed word - '.AbstractLoggedException::$errorMessage.' - detected in HTTP input');
        }


      }

?>