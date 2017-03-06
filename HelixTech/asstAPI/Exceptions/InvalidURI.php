<?php namespace HelixTech\asstAPI\Exceptions;

      /**
       * Logged Exception: Attempted Access to Invalid URI
       * @todo error logging
       */
      class InvalidURI extends AbstractLoggedException
      {

          /**
           * Log attempts to access invalid URLs
           * This might indicate either that a developer is consuming the API poorly,
           * Or that an attacker is attempting to explore vulnerabilities
           *
           * @todo write function to log invalid URI expcetions
           *
           *
           */
          public static function logError(){

          }


      }

?>
