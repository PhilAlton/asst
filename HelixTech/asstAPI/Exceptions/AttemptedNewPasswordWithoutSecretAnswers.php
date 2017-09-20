<?php namespace HelixTech\asstAPI\Exceptions;

      /**
       * Summary of AttemptedNewPasswordWithoutSecretAnswers - log attempts to reset password circumnavigating safeguards
       */
      class AttemptedNewPasswordWithoutSecretAnswers extends AbstractLoggedException
      {
          /**
           * Summary of AttemptedNewPasswordWithoutSecretAnswers
           * This is a good indication of a attempted hack, and therefore a specific notification will be sent to
           * the system administrator, via slack.
           */
          public function logError(){
            AbstractLoggedException::$dbMessage .= "Password reset attempted without secret answers; ";
            parent::log();
    
            // ALert system admin via slack
            parent::callSlack('Password reset attempted without secret answers - '.AbstractLoggedException::$errorMessage.' - detected');
        }


      }

?>