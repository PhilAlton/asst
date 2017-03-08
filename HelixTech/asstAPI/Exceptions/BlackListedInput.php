<?php namespace HelixTech\asstAPI\Exceptions;

      /**
       * Summary of BlackListedInput - log attempts to send blacklisted input to the server
       * @todo error logging
       */
      class BlackListedInput extends AbstractLoggedException
      {
          /**
           * Summary of BlackListedInput
           * @todo connect to the database to store log info for attempts to send blacklisted input to the server
           * This is a good indication of a attempted hack, and therefore a specific notification will be sent to 
           * the system administrator
           */
          public static function logError(){
              // Log in Database


              // ALert system admin
              // E.g Via slack


          }


      }

?>