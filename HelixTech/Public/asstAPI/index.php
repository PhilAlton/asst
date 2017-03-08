<?php
/**
 * Landing point
 * Establish safe connection
 * Route URL and map to appropriate function
 * Process output
 * 
 * @todo rewrite routing
 * @todo link to connection class
 * 
 * @author Philip Alton
 * @copyright Helix Tech Ltd. 2017
 * @file index.php
 * @package asstAPI
 * 
 * 
 */

require_once dirname(dirname(__FILE__)) . '/../bootstrap.php';

use HelixTech\asstAPI\{Output};
use HelixTech\asstAPI\Models\{Data, User};
use HelixTech\asstAPI\Exceptions\{UnableToAuthenticateUserCredentials, InsecureConnection, InvalidURI};



try {
    if(!isset($_SERVER['HTTPS'])){throw new InsecureConnection("Connection must be established via HTTPS");} // ensure connection via HTTPS



    // get the HTTP method, path and body of the request
    $method = $_SERVER['REQUEST_METHOD'];
    $request = explode('/', trim($_SERVER['REQUEST_URI'],'/'));
    $apiRoot = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
    $input = json_decode(file_get_contents('php://input'),true);

    // sanitise POST data UserName
    $input['UserName'] = $_SERVER["PHP_AUTH_USER"];             // This should never be sent in the post variables, instead, username should be sent in the header.
    $input['Password'] = $_SERVER["PHP_AUTH_PW"];               // This also prevents UserName being updated.
    // A new password may be (in the future) sent via POST, but for now, this should not be updatable through this method.



    try{
        if (!isset($_SERVER["PHP_AUTH_USER"])) {throw new UnableToAuthenticateUserCredentials ("User details not sent in header");}
        // Sanitise input of UserName
        $_SERVER["PHP_AUTH_USER"] = filter_var(filter_var($_SERVER["PHP_AUTH_USER"], FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);

        // Switch to govern action based on URI
        try{
	        if (uri('asst/Users/..*') and ($request[2]==$_SERVER["PHP_AUTH_USER"]))       // ensure that user specific end points are only accesible
            {
                $UserName = $request[2];
		        if (isset($request[3]))
                {

                    switch($request[3])
                    {
				        case "data":
					        // case for /asst/Users/Id/Data pass in $method     "/asst/Users/$UserName/data"
					        Data::syncData($UserName);
					        break;
                        case "resetPassword":
                            //case for restetting password  "/asst/Users/$UserName/resetPassword"
                            User::resetPassword($UserName);
                            break;
				        default:
					        throw new InvalidURI("Invalid URI selected".$_SERVER['REQUEST_URI']);
			        }

                } else {
			        // action for /asst/Users/Id
			        User::handleRequest($method, $UserName, $input);
		        }

	        } elseif (uri('asst/Users')){
		        // code for asst/Users (create new user)
		        User::createUser($input);

	        } else {

                throw new InvalidURI( "Invalid URI selected".$_SERVER['REQUEST_URI']);

	        }


        }
        catch (InvalidURI $e) {
            http_response_code(404);
            Output::errorMsg("caught exception: ".$e->getMessage().".");
        }


    }
    catch (UnableToAuthenticateUserCredentials $e) {
        http_response_code(403);
        Output::errorMsg("Unable to authenticate: ".$e->getMessage().".");
    }
}
catch (Exception $e){
        http_response_code(403);
        Output::errorMsg("Connection Failure: ".$e->getMessage().".");
    }


Output::go();


function uri($match){
	$match = "#".$match."#";
	return preg_match($match, $_SERVER['REQUEST_URI']);

}



?>