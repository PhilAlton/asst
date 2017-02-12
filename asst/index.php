<?php
require 'models.php';


// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['REQUEST_URI'],'/'));
$apiRoot = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
$input = json_decode(file_get_contents('php://input'),true);

// Switch to govern action based on URI
try{
	if (uri('asst/Users/..*')){
	//	echo "/asst/Users/*";
	$uID = $request[2];
		if (isset($request[3])){
			switch($request[3]){
				case "data":
					// case for /asst/Users/Id/Data pass in $method
					echo "/asst/Users/Id/Data";
					Data::syncData($uID);
					break;
				case "auth":
					// case for /asst/Users/Id/auth
					echo "/asst/Users/Id/auth";
					User::authenticate($uID);
					break;
				default:
					$e = "Invalid URI selected".$_SERVER['REQUEST_URI'];
					throw new Exception($e);
			}
		} else {
			// action for /asst/Users/Id
			echo "/asst/Users/Id";
			echo $uID;
			User::handleRequest($method, $uID);

		}

	} else if (uri('asst/Users')){
		// code for asst/Users (create new user)
		User::createUser();
		echo "/asst/Users";

	} else {
		$e = "Invalid URI selected";
		throw new Exception($e);
	}
} catch (Exception $e) {
		echo "caught exception: ", $e->getMessage(), "\n";
}



function uri(String $match){
	$match = "#".$match."#";
	return preg_match($match, $_SERVER['REQUEST_URI']);

}
















/*
// retrieve the table and key from the path

//echo "hereis".$apiRoot;
echo "</br></br></br>";
var_dump($request);
$table = array_shift($request);
$key = array_shift($request);
//echo("</br>".$table."</br>H".$key."</br>");


// escape the columns and values from the input object
$columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
var_dump($columns);
$values = array_map(function ($value) use ($link) {
  if ($value===null) return null;
  return mysqli_real_escape_string($link,(string)$value);
},array_values($input));

//echo("other");
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
  if (!$key) echo '[';
  for ($i=0;$i<mysqli_num_rows($result);$i++) {
    echo ($i>0?',':'').json_encode(mysqli_fetch_object($result));
  }
  if (!$key) echo ']';
} elseif ($method == 'POST') {
  echo mysqli_insert_id($link);
} else {
  echo mysqli_affected_rows($link);
}
 
// close mysql connection
mysqli_close($link);
*/
?>