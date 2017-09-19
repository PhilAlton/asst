<?php namespace HelixTech\asstAPI\Exceptions;

      /**
       * Summary of RequestPasswordResetForNonExistantUser - log attempts to reset password for non-existant users
       */
      class RequestPasswordResetForNonExistantUser extends AbstractLoggedException
      {
          /**
           * Summary of RequestPasswordResetForNonExistantUser
           */
          public function logError(){
            AbstractLoggedException::$dbMessage .= AbstractLoggedException::$errorMessage."; ";
            parent::log();
    
          
        }


      }

?>