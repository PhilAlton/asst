<?php namespace HelixTech\asstAPI;
/** File to handle data input, and sanitization / filtering
 *  Includes class representing the HTTP connection to the server
 *
 * @author Philip Alton
 * @copyright Helix Tech Ltd. 2017
 * @file Connection.php
 * @package asstAPI
 *
 * @todo write connection class
 *
 */

use HelixTech\asstAPI\Exceptions\{UnableToAuthenticateUserCredentials, InsecureConnection, BlackListedInput};
use HelixTech\asstAPI\{Query, User, Crypt};


/**
 * Connection class to register, store and error log connection details
 * Sanitize input through connection class
 */
class Connection{

    private static $method; public static function getMethod(){return Connection::$method;}
    private static $request; public static function getRequest(){return Connection::$request;}
    private static $input; public static function getInput(){return Connection::$input;}
    private static $apiRoot; public static function getAPIroot(){return Connection::$apiRoot;}

    private static $ip; public static function getIP(){return Connection::$ip;}
    private static $UserName; public static function getUserName(){return Connection::$UserName;}
    private static $password; public static function getPassword(){return Connection::$password;}
    private static $connectionTime; public static function getConnectionTim(){return Connection::$connectionTime;}
    private static $uri; public static function getURI(){return Connection::$uri;}

    /**  @var mixed $cID - ID of the Connection in the Database */
    private static $cID;



    public static function connect(){

        try {


            // get the HTTP method, path and body of the request
            Connection::$connectionTime = $_SERVER['REQUEST_TIME'];
            Connection::$method = $_SERVER['REQUEST_METHOD'];
            Connection::$uri = $_SERVER['REQUEST_URI'];
            Connection::$request = explode('/', trim($_SERVER['REQUEST_URI'],'/'));
            Connection::$apiRoot = preg_replace('/[^a-z0-9_]+/i','',array_shift(Connection::$request));
            Connection::analyse(file_get_contents('php://input'));

            // ensure connection via HTTPS
            if(!isset($_SERVER['HTTPS'])){
                throw new InsecureConnection("Connection must be established via HTTPS");
            }

            //ensure connected with UserName and Password
            if (!isset($_SERVER["PHP_AUTH_USER"])){
                throw new UnableToAuthenticateUserCredentials ("User details not sent in header");
            }

            Connection::sanitize();


        } catch (InsecureConnection $e){
            http_response_code(403);
            Output::errorMsg("Connection Failure: ".$e->getMessage().".");
        } catch (UnableToAuthenticateUserCredentials $e) {
            http_response_code(403);
            Output::errorMsg("Unable to authenticate: ".$e->getMessage().".");
        }


        Connection::storeConnection();

    }


    private static function analyse($input){

        try{



            Connection::$input = json_decode($input);

        } catch (BlackListedInput $e){
            header("HTTP/1.0 418 I'm A Teapot");
            Output::errorMsg("Connection Failure: "
                                ."BLACK LISTED INPUT DETECTED"
                                ."System Administrator notified."
            );
        }

    }




    private static function sanitize(){

        // Sanitise input of UserName
        $_SERVER["PHP_AUTH_USER"] = filter_var(filter_var($_SERVER["PHP_AUTH_USER"], FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);


        // sanitise POST data UserName
        Connection::$input['UserName'] = $_SERVER["PHP_AUTH_USER"];             // This should never be sent in the post variables, instead, username should be sent in the header.
        Connection::$input['Password'] = $_SERVER["PHP_AUTH_PW"];               // This also prevents UserName being updated.
        // A new password may be (in the future) sent via POST, but for now, this should not be updatable through this method.

    }




    private static function storeConnection(){

        Connection::$UserName = $_SERVER["PHP_AUTH_USER"];
        Connection::$password = $_SERVER["PHP_AUTH_PW"];
        Connection::$ip = $_SERVER['REMOTE_ADDR'];


        $query = New Query(
            INSERT, "INTO ConnectionLog".
            "(CXTN_USER, CXTN_IP, CXTN_REQUEST)".
            "VALUES (:UserName, :ip, :Request)"
        );

        $query->execute([
            ':UserName' => Connection::$UserName,
            ':ip' => Connection::$ip,
            ':Request' => Connection::$uri
            ]
        );

        Connection::$cID = $query->lastInsertId();



    }


    /**
     * Summary of authenticate: called when other class wishes to give connection access to protected resources
     * @throws \UnexpectedValueException
     * @return boolean $q_auth (success vs failure)
     */
    public static function authenticate(){

        // authenticate user session to enable access to api functions
        $q_auth = false;

        try{
            // retrieve stored password string from database against UserName
            $query = New Query(SELECT, 'UniqueID, Password FROM `AuthTable` WHERE `UserName` =:UserName');
            $UserDetails = $query->execute([':UserName' => $_SERVER["PHP_AUTH_USER"]]);


            /** @todo If control block will need to go into query class for null outputs, as this is where decryption will occur */
            if (count($UserDetails)===0){
                // If no password obtained then throw exception and handle.
                $e = $_SERVER['PHP_AUTH_USER']." DOES NOT EXIST";
                throw new \UnexpectedValueException($e);
            } else {
                // Else decrypt the password
                $password = Crypt::decrypt($UserDetails["Password"]);                                                             //FIX - decrypt should go in query class
            }

            // Check if the hash of the entered login password, matches the stored hash.
            if (password_verify(
                    base64_encode(hash('sha384', $_SERVER["PHP_AUTH_PW"], true)),
                    $password
                ))

                    {   // Success
                        User::$uID = $UserDetails["UniqueID"];
                        $query = New Query(UPDATE, "ConnectionLog ".
                                           "SET CXTN_AUTHENTIC=1".
                                           "WHERE `CXTN_ID` =:cID");
                        $query->execute([':cID' => Connection::$cID]);
                        $q_auth = true;

            } else {    // Failure
                        http_response_code(401); // not authorised
                        $query = New Query(UPDATE, "ConnectionLog ".
                                       "SET CXTN_AUTHENTIC=0".
                                       "WHERE `CXTN_ID` =:cID");
                        $query->execute([':cID' => Connection::$cID]);
                        $q_auth = false;
            }


        }
        catch (UnexpectedValueException $e) {
            http_response_code(401);
            Output::errorMsg("Unexpected Value: ".$e->getMessage().".");
        }

        return $q_auth;

    }


}




?>