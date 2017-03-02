<?php
require 'output.php';
require 'models.php';
require 'crypt.php';


//Test segments
/*
echo "</br>".strlen(encrypt(password_hash(base64_encode(hash('sha384', "the cat and the dog", true)),PASSWORD_DEFAULT)));
echo "</br>".strlen(encrypt(password_hash(base64_encode(hash('sha384', "", true)),PASSWORD_DEFAULT)));
echo "</br>".strlen(encrypt(password_hash(base64_encode(hash('sha384', null, true)),PASSWORD_DEFAULT)));
echo "</br>".strlen(encrypt(password_hash(base64_encode(hash('sha384', "3", true)),PASSWORD_DEFAULT)));
echo "</br>".strlen(encrypt(password_hash(base64_encode(hash('sha384', 3, true)),PASSWORD_DEFAULT)));
echo "</br>".strlen(encrypt(password_hash(base64_encode(hash('sha384', "endofthree", true)),PASSWORD_DEFAULT)));
*/



// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['REQUEST_URI'],'/'));
$apiRoot = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
$input = json_decode(file_get_contents('php://input'),true);

// sanitise POST data UserName
$input['UserName'] = null;          // This should never be sent in the post variables, instead, username should be sent in the header.
                                    // This also prevents UserName being updated.
$input['Password'] = null;          // A new password may be (in the future) sent via POST, but for now, this should not be updatable through this method.



try{
    if (!isset($_SERVER["PHP_AUTH_USER"])) {$e = "User details not sent in header"; throw new OutOfRangeException ($e);}
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
					    throw new Exception("Invalid URI selected".$_SERVER['REQUEST_URI']);
			    }

            } else {
			    // action for /asst/Users/Id
			    User::handleRequest($method, $UserName, $input);
		    }

	    } else if (uri('asst/Users')){
		    // code for asst/Users (create new user)
		    User::createUser($input);

	    } else {
		    $e = "Invalid URI selected".$_SERVER['REQUEST_URI'];
		    throw new Exception($e);

	    }


    } catch (Exception $e) {
            http_response_code(406);
		    Output::errorMsg("caught exception: ".$e->getMessage().".");
    }


}
catch (OutOfRangeException $e) {
    http_response_code(401);
    Output::errorMsg("Unable to authenticate: ".$e->getMessage().".");
}


Output::go();


function uri($match){
	$match = "#".$match."#";
	return preg_match($match, $_SERVER['REQUEST_URI']);

}



?>