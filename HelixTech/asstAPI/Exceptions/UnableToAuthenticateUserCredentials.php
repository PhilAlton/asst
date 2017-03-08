<?php namespace HelixTech\asstAPI\Exceptions;

use HelixTech\asstAPI\Connection;
/**
 * Logged Exception: Unable to Authenticate
 * @todo error logging
 */
class UnableToAuthenticateUserCredentials extends AbstractLoggedException
{

    /**
     * Summary of logError - log failures to authenticate user details
     * @todo connect to the database to store log info
     */
    public static function logError(){



// the following query will then read as specified in execute

        $query = New Query(UPDATE, "ConnectionLog ".
                           "SET CXTN_AUTHENTIC=:value ".
                           "WHERE `UniqueID` =:uID");
		$query->execute([":value" => false, ":uID" => Connection::getCID()]);





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


        */
    }




}


?>