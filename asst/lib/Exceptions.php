<?php
/**
 * Define custom exception classes
 *
 */


/**
 * Abstract class for exceptions which need logging
 */
abstract class LoggedException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = NULL){

        // ensure proper logging of error
        Self::log();
        parent::__construct($message, $code, $previous);

    }


    abstract public static function log();

}

/**
 * Unable to Authenticate
 */
class UnableToAuthenticateUserCredentials extends LoggedException
{

    public static function log(){

    //    $query = new Query(SELECT, "LAST_INSERT_ID()");   /// will not work as cannot specify table
                                                                // need to create a connection class to wrap all the
                                                                // relavent connection variables. This should be a singleton instance
                                                                // this class should contain all the relavent connection details, including
                                                                // the Unique ID of the connection database representation
   //     $uID = $query->execute();
                                                                // the following query will then read as specified in execute

        $query = New Query(UPDATE, "ConnectionLog ".
                           "SET CXTN_AUTHENTIC=:value ".
                           "WHERE `UniqueID` =:uID");
		$query->execute([":value" => false, ":uID" => Connection::getConnection()->getID()]);





        // the query itself should be wrapped in the class therefore, and used as follows;
        Connection::getConnection()->authenticationFailed();

        //:
            public function authenticationFailed(){
                self::CXTN_AUTHENTIC = false;
                self::update_CXTN_AUTHENTIC();
            }

            public function update_CXTN_AUTHENTIC(){

                $query = New Query(UPDATE, "ConnectionLog ".
                           "SET CXTN_AUTHENTIC=:value ".
                           "WHERE `UniqueID` =:uID");
		        $query->execute([":value" => self::CXTN_AUTHENTIC, ":uID" => SELF::getID()]);

            }

        //:: also
            public function authenticationSuccess(){
                self::CXTN_AUTHENTIC = true;
                self::update_CXTN_AUTHENTIC();
            }

    }




}


?>