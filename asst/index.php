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

// Sanitise input

// $_SERVER["PHP_AUTH_USER"]
// $_SERVER["PHP_AUTH_PW"]


// Switch to govern action based on URI
try{
	if (uri('asst/Users/..*')){
	//	Output::setOutput( "/asst/Users/*";
	$UserName = $request[2];
		if (isset($request[3])){
			switch($request[3]){
				case "data":
					// case for /asst/Users/Id/Data pass in $method
					Output::setOutput("/asst/Users/$UserName/data");                    //this line should be removed
					Data::syncData($UserName);
					break;
                case "resetPassword":
                    //case for restetting password
                    Output::setOutput("/asst/Users/$UserName/resetPassword");           //this line should be removed
                    User::resetPassword($UserName);
                    break;
				default:

					$e = "Invalid URI selected".$_SERVER['REQUEST_URI'];
					throw new Exception($e);
			}
		} else {
			// action for /asst/Users/Id
	//		Output::setOutput("/asst/Users/Id"."</br>");
	//		Output::setOutput($uID);
			User::handleRequest($method, $UserName, $input);

		}

	} else if (uri('asst/Users')){
		// code for asst/Users (create new user)
		User::createUser($input);
		//Output::setOutput("/asst/Users");


	} else {
		$e = "Invalid URI selected".$_SERVER['REQUEST_URI'];
		throw new Exception($e);
	}
} catch (Exception $e) {
        http_response_code(406);
		Output::errorMsg("caught exception: ".$e->getMessage().".");
}



Output::go();


function uri(String $match){
	$match = "#".$match."#";
	return preg_match($match, $_SERVER['REQUEST_URI']);

}
















/*
// retrieve the table and key from the path

//Output::setOutput( "hereis".$apiRoot;
Output::setOutput( "</br></br></br>";
var_dump($request);
$table = array_shift($request);
$key = array_shift($request);
//Output::setOutput(("</br>".$table."</br>H".$key."</br>");


// escape the columns and values from the input object
$columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
var_dump($columns);
$values = array_map(function ($value) use ($link) {
  if ($value===null) return null;
  return mysqli_real_escape_string($link,(string)$value);
},array_values($input));

//Output::setOutput(("other");
/*var_dump($vales);
 
// build the SET part of the SQL command
$set = '';
for ($i=0;$i<count($columns);$i++) {
  $set.=($i>0?',':'').'`'.$columns[$i].'`=';
  $set.=($values[$i]===null?'NULL':'"'.$values[$i].'"');
}



// create SQL based on HTTP method
switch ($method) {
  case 'GET':
    $sql = "select * from `$table`".($key?" WHERE id=$key":''); break;
  case 'PUT':
    $sql = "update `$table` set $set where id=$key"; break;
  case 'POST':
    $sql = "insert into `$table` set $set"; break;
  case 'DELETE':
    $sql = "delete `$table` where id=$key"; break;
}
 
// excecute SQL statement
$result = mysqli_query($link,$sql);
 
// die if SQL statement failed
if (!$result) {
  http_response_code(404);
  die(mysqli_error());
}
 
// print results, insert id or affected row count
if ($method == 'GET') {
  if (!$key) Output::setOutput( '[';
  for ($i=0;$i<mysqli_num_rows($result);$i++) {
    Output::setOutput( ($i>0?',':'').json_encode(mysqli_fetch_object($result));
  }
  if (!$key) Output::setOutput( ']';
} elseif ($method == 'POST') {
  Output::setOutput( mysqli_insert_id($link);
} else {
  Output::setOutput( mysqli_affected_rows($link);
}
 
// close mysql connection
mysqli_close($link);
*/
?>