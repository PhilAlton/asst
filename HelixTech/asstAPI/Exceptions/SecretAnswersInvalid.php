<?php namespace HelixTech\asstAPI\Exceptions;

      /**
       * Summary of SecretAnswersInvalid - log attempts to reset password for non-existant users
       */
      class SecretAnswersInvalid extends AbstractLoggedException
      {
          /**
           * Summary of SecretAnswersInvalid
           */
          public function logError(){
            AbstractLoggedException::$dbMessage .= AbstractLoggedException::$errorMessage."; ";
            parent::log();
    
          
        }


      }

?>