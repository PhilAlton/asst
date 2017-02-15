<?php
require 'query.php';

class User {



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
				User::updateParams($uID, $params);
				break;

			case 'DELETE':
				// call method to delete
				User::deleteUser($uID);
				break;

			case 'GET':
				// call method to get
				User::getRepresentation($uID);
				break;

			default:
				// throw exception

		}


		return 'need to return json representation or success string';


	}




	private static function getRepresentation($uID){
		// GET request

		$query = New Query('SELECT * FROM `UserTable` WHERE `ID` =:id');
		Output::setOutput($query->execute([':id' => $uID]));

	}

	private static function updateParams($uID, $params){
		//PUT request, acepting multiple arguments including user ID.


	}


	private static function deleteUser($uID){
		// DELETE request, accepting user ID;

	}




}



class Data {


	public static function syncData(){
		// method to get user data against timestamp and either update (call postData),
		//	or withdraw any additional server data and pass back to user
	}






}


?>