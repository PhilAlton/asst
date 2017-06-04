<?php namespace HelixTech\asstAPI\Exceptions;

      /**
       * Summary of ConnectionFailed - log failures to connect to the API
       */
      class ConnectionFailed extends AbstractLoggedException
      {
        /**
         * Summary of ConnectionFailed
         *
         */
        public function logError(){
            AbstractLoggedException::$dbMessage .= "Connection Failed; ";
            parent::log();
        }

      }

?>