<?php namespace HelixTech\asstAPI;
/** File to handle data input, and sanitization / filtering
 *  Includes class representing the HTTP connection to the server
 *
 * @author Philip Alton
 * @copyright Helix Tech Ltd. 2017
 * @file Connection.php
 * @package asstAPI
 *
 *
 */

use HelixTech\asstAPI\Exceptions\{UnableToAuthenticateUserCredentials, InsecureConnection, BlackListedInput};
use HelixTech\asstAPI\{Query, Crypt};
use HelixTech\asstAPI\Models\User;


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
    private static $connectionTime; public static function getConnectionTime(){return Connection::$connectionTime;}
    private static $uri; public static function getURI(){return Connection::$uri;}

    private static $established = true; public static function isEstablished(){return Connection::$established;}

    /**  @var mixed $cID - ID of the Connection in the Database */
    private static $cID; public static function getCID(){return Connection::$cID;}



    public static function connect(){

        try {

            // get the HTTP method, path and body of the request
            Connection::$connectionTime = $_SERVER['REQUEST_TIME'];
            Connection::$method = $_SERVER['REQUEST_METHOD'];

            Connection::analyse($_SERVER['REQUEST_URI']);
            Connection::$uri = $_SERVER['REQUEST_URI'];
            $splitReqGet = explode('?', $_SERVER['REQUEST_URI']);
            Connection::$request = explode('/', trim($splitReqGet[0],'/'));
            Connection::$apiRoot = preg_replace('/[^a-z0-9_]+/i','',array_shift(Connection::$request));

            $input = file_get_contents('php://input');
            Connection::analyse($input);
            Connection::analyse(json_encode($_GET));

            $input = json_decode($input, true);

            Connection::$input = array();
            Connection::$input = !is_array($input) ? Connection::$input : array_merge(Connection::$input, $input);
            Connection::$input = !is_array($_GET) ? Connection::$input : array_merge(Connection::$input, $_GET);

            // ensure connection via HTTPS
            if(!isset($_SERVER['HTTPS'])){
                throw new InsecureConnection("Connection must be established via HTTPS");
            }

            //ensure connected with UserName
            if (!isset($_SERVER["PHP_AUTH_USER"])){
                throw new UnableToAuthenticateUserCredentials ("User details not sent in header");
            }

            Connection::sanitize();

        } catch (InsecureConnection $e){
            http_response_code(403);
            Connection::$established = false;
            Output::errorMsg("Connection Failure: ".$e->getMessage().".");
        } catch (UnableToAuthenticateUserCredentials $e) {
            http_response_code(403);
            Connection::$established = false;
            Output::errorMsg("Unable to authenticate: ".$e->getMessage().".");
        }

        Connection::storeConnection();

    }


    private static function analyse($input){

        $blackList = array(
            "DROP",
            "INSERT",
            "DELETE",
            "SELECT",
            "alert(",
            "<",">",
            "://"
        );

        try{
            foreach ($blackList as $blackWord){
                if (!(strpos($input, $blackWord) === false)){
                    throw new BlackListedInput($blackWord);
                }
            }

        } catch (BlackListedInput $e){
            header("HTTP/1.0 418 I'm A Teapot");
            Connection::$established = false;
            Output::errorMsg("Connection Failure: "
                                ."BLACK LISTED INPUT DETECTED: "
                                ."'".$e->getMessage()."'"." found in input: "
                                .$input." - System Administrator notified."
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
        Connection::$UserName = (isset($_SERVER["PHP_AUTH_USER"])) ? $_SERVER["PHP_AUTH_USER"] : "ANON";
        Connection::$password = (isset($_SERVER["PHP_AUTH_PW"])) ? $_SERVER["PHP_AUTH_PW"] : "NOT SENT";
        Connection::$ip = $_SERVER['REMOTE_ADDR'];


        $query = New Query(
            INSERT, "INTO ConnectionLog ".
            "(CXTN_USER, CXTN_IP, CXTN_REQUEST) ".
            "VALUES (:UserName, :ip, :Request)"
        );

        $query->silentexecute(SIMPLIFY_QUERY_RESULTS_ON,  [
            ':UserName' => Connection::$UserName,
            ':ip' => Connection::$ip,
            ':Request' => Connection::$method."@".Connection::$uri
            ]
        );

        Connection::$cID = $query->lastInsertId();



    }


    /**
     * Summary of authenticate: called when other class wishes to give connection access to protected resources
     * @throws \UnexpectedValueException
     * @return boolean $q_auth (success vs failure)
     */
    public static function authenticate($table = 'AuthTable'){

        // authenticate user session to enable access to api functions
        $q_auth = false;

        try{        

            // retrieve stored password string from database against UserName
            $query = New Query(SELECT, "* FROM `$table` WHERE `UserName` =:UserName");
            $UserDetails = $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UserName' => $_SERVER["PHP_AUTH_USER"]]);

            /** @todo If control block will need to go into query class for null outputs, as this is where decryption will occur */
            if (count($UserDetails)===0){
                // If no password obtained then throw exception and handle.
                $e = $_SERVER['PHP_AUTH_USER']." DOES NOT EXIST";
                throw new \UnexpectedValueException($e);
            } else {
                // Else decrypt the password

                    // for admin table, key is protected by password
                if ($table == "AdminTable"){
                    Crypt::decryptWithUserKey($UserDetails["UserKey"], $_SERVER["PHP_AUTH_PW"]);
                }

                //Load either the authToken from the database, or the password, depending on which the user has supplied
                $password = (strpos($_SERVER["PHP_AUTH_PW"], $_SERVER["PHP_AUTH_USER"]."=") !== False) ? $UserDetails["AuthToken"] : $UserDetails["Password"];
                var_dump($password);
				$password = Crypt::decrypt($password);                                                             //FIX - decrypt should go in query class
            
				$a = Array(
					Crypt::decrypt("def50200cdf075500a74b42b5b62c2f038c9ef0a59cb19475944d7efb0e5d7b0"),
					Crypt::decrypt("def50200d31af6bd8273bc9803fc65cbcbcf1e15d10d48609197ec6b6c1d0ee1e6e56d011f16883abb53759ccf144b3c251bb7714997076225579582b42797a28e069eb0ea015d8bb7bee400fd1acb2af3527c496e1005b406848683f210a83b727d4d4e7c64c61794e1e006babe536255ce9de2cec0744757c0ab6db4bf03ee42062e2007f70504b616110c86885a24"),
					Crypt::decrypt("def50200782defb75f3f97b88dfdf13e487913942e55d1ebe5639b55cb44ac62"),
					Crypt::decrypt($password),
					Crypt::decrypt("def50200f9316cff3301dc55b1821f51c314733431ab9e146fbec3bbdb1a2453714f0517e324cfc332bd2d1501119b5586acebe3af680221ba77b6756fc70dbeed5a7db6eeaf450768a429d7d4055689bd6374d03446b398e3114b4cc0b79e7c61559fb6e14926bfe69c0d1c1161e7b0290a895ef5a40a1de0a5c0919047ebf7f4e3a9e80d1b3e83573584b93aa83eaf")
				);

				var_dump($a);
			}

			var_dump($password);
            // Check if the hash of the entered login password, matches the stored hash.
            if (password_verify(
                    base64_encode(hash('sha384', $_SERVER["PHP_AUTH_PW"], true)),
                    $password
                )){
                User::$uID = $UserDetails["UniqueID"];
                Connection::authentic();
                $q_auth = true;

            } else {
                Connection::notAuthentic();
                $q_auth = false;
            }

        }
        catch (\UnexpectedValueException $e) {
            http_response_code(401);
            Output::errorMsg("Unexpected Value: ".$e->getMessage().".");
        }

        return $q_auth;

    }


    private static function authentic(){
        // Success
        $query = New Query(UPDATE, "ConnectionLog ".
                           "SET CXTN_AUTHENTIC=1 ".
                           "WHERE `CXTN_ID` =:cID");
        $query->silentexecute(SIMPLIFY_QUERY_RESULTS_ON,  [':cID' => Connection::$cID]);
        $q_auth = true;

    }


    public static function notAuthentic(){
        // Failure
        http_response_code(401); // not authorised
        $query = New Query(UPDATE, "ConnectionLog ".
                       "SET CXTN_AUTHENTIC=0 ".
                       "WHERE `CXTN_ID` =:cID");
        $query->silentexecute(SIMPLIFY_QUERY_RESULTS_ON,  [':cID' => Connection::$cID]);


    }


}




?>