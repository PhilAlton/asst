<?php namespace HelixTech\asstAPI\Exceptions;

      /**
       * Summary of AttemptedUseOfExpiredPasswordResetToken - log attempts to reset password for non-existant users
       */
      class AttemptedUseOfExpiredPasswordResetToken extends AbstractLoggedException
      {
          /**
           * Summary of AttemptedUseOfExpiredPasswordResetToken
           */
          public function logError(){
            AbstractLoggedException::$dbMessage .= AbstractLoggedException::$errorMessage."; ";
            parent::log();
    
          
        }


      }

?>