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

use HelixTech\asstAPI\Exceptions\{UnableToAuthenticateUserCredentials, InsecureConnection, BlackListedInput, UsernameNotAValidEmailAddress};
use HelixTech\asstAPI\{Query, Crypt};
use HelixTech\asstAPI\Models\User;
use Google\Auth;

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
    private static $userAgent; public static function getUserAgent(){return Connection::$userAgent;}
    private static $UserName; public static function getUserName(){return Connection::$UserName;}
    private static $password; public static function getPassword(){return Connection::$password;}
	private static $AuthToken; public static function getAuthToken(){return Connection::$AuthToken;}
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
            Connection::$userAgent = $_SERVER['HTTP_USER_AGENT'];

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
			Connection::$AuthToken = false;

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
        } catch (UsernameNotAValidEmailAddress $e){
			http_response_code(406);
            Connection::$established = false;
            Output::errorMsg("Credential Failure: ".$e->getMessage().".");
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

		if (!$_SERVER["PHP_AUTH_USER"]){
			// if email validation has failed
			throw new UsernameNotAValidEmailAddress ("Submitted UserName is not a valid email address");
		}

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
            "(CXTN_USER, CXTN_IP, CXTN_USERAGENT, CXTN_REQUEST) ".
            "VALUES (:UserName, :ip, :UserAgent, :Request)"
        );

        $query->silentexecute(SIMPLIFY_QUERY_RESULTS_ON,  [
            ':UserName' => Connection::$UserName,
            ':ip' => Connection::$ip,
            ':UserAgent' => Connection::$userAgent,
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
                // If no user obtained from database then throw exception and handle.
                $e = $_SERVER['PHP_AUTH_USER']." DOES NOT EXIST";
                throw new \UnexpectedValueException($e);
            } else {
                // Else decrypt the password

                //Load either the authToken from the database, or the password, depending on which the user has supplied
				if (strpos($_SERVER["PHP_AUTH_PW"], $_SERVER["PHP_AUTH_USER"]."=") !== False){
					// Token has been supplied
					$password = $UserDetails["AuthToken"];
				} elseif (strpos($_SERVER["PHP_AUTH_PW"], "google=") !== False) {
					$client = new Google_Client(['client_id' => $_SERVER["PHP_AUTH_PW"]]);//less "google="
					//$payload = $client->verifyIdToken($id_token);
					//if ($payload) {
 					//  $userid = $payload['sub'];
				//		var_dump($payload);
				//	} else {
 					  // Invalid ID token
					}
				} else {
					// Password has been supplied
					$password = $UserDetails["Password"];
					//Return authtoken to be used in future requests, unless connection is via admin rather than user
					if($table != "AdminTable"){Connection::$AuthToken = $UserDetails["AuthTokenPlain"];}
				}


				// for admin table, key is protected by password
                if ($table == "AdminTable"){
                    $password = Crypt::decryptWithUserKey($UserDetails["UserKey"], $_SERVER["PHP_AUTH_PW"], $password);
                }

			}

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