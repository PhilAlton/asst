<?php
require 'query.php';
require_once 'database.php';


class User {

	public $database = new Database;


	public static function authenticate($uID){
		// authenticate user session to enable access to api functions

	}


	public static function createUser(){
		// code to create new user, i.e update userTable and create new unique table



	}

	public static function handleRequest($method, $uID, $params = null){

		// ?need to instantiate instance of model
		$return;

		switch ($method) {
/*			case 'PUT':
				// call method to replace
				updateParams();
				break;
*/
			case 'POST':
				// call method to update single varaibles
				updateParams();
				break;

			case 'DELETE':
				// call method to delete
				deleteUser();
				break;

			case 'GET':
				// call method to get
				getRepresentation();
				break;

			default:
				// throw exception

		}


		return 'need to return json representation or success string';


	}



	public function __construct($representation){
		// accept class variables as JSON string and decode here to construct object

		// remember to create new SQL table representing user data as well as adding user details
		//		to master table


		// send back secret id (formed from PHP random unique ID generator (+ name?)




	}


	private static function getRepresentation($uID){
		// GET request

		$query = New Query('SELECT * FROM `UserTable` WHERE `ID` =:id');
		return $query->execute([':id' => $uID]);

	}

	private static function updateParams(){
		//PUT request, acepting multiple arguments including user ID.


	}


	private static function deleteUser(){
		// DELETE request, accepting user ID;

	}




}



class Data {

	public $answersTosurveryinlotsofvariables;

	public function __construct(){


	}



	public static function syncData(){
		// method to get user data against timestamp and either update (call postData),
		//	or withdraw any additional server data and pass back to user
	}






}


?>