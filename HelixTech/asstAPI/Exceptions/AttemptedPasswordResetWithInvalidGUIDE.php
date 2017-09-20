<?php namespace HelixTech\asstAPI\Exceptions;

      /**
       * Summary of AttemptedPasswordResetWithInvalidGUIDE - log attempts to reset password circumnavigating safeguards
       */
      class AttemptedPasswordResetWithInvalidGUIDE extends AbstractLoggedException
      {
          /**
           * Summary of AttemptedPasswordResetWithInvalidGUIDE
           * This is a good indication of a attempted hack, and therefore a specific notification will be sent to
           * the system administrator, via slack.
           */
          public function logError(){
            AbstractLoggedException::$dbMessage .= "Invalid GUIDE attempted to reset password; ";
            parent::log();
    
            // ALert system admin via slack
            parent::callSlack('Invalid GUIDE attempted to reset password - '.AbstractLoggedException::$errorMessage.' - detected');
        }


      }

?>