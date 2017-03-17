<?php namespace HelixTech\asstAPI\Exceptions;

      /**
       * Summary of BlackListedInput - log attempts to send blacklisted input to the server
       * @todo system admin notification
       */
      class DecryptionFailureInvalidKeyOrCorruptData extends AbstractLoggedException
      {
          /**
           * Summary of BlackListedInput
           * @todo connect to the database to store log info for attempts to send blacklisted input to the server
           * This is a good indication of a attempted hack, and therefore a specific notification will be sent to
           * the system administrator, via slack.
           */
          public static function logError(){
            AbstractLoggedException::$dbMessage .= "Decrpytion failure; ";
            parent::log();
    
            // ALert system admin via slack
            parent::callSLack('Decryption failure - Database corrupt or Key invalid');
        }


      }

?>