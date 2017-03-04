<?php namespace HelixTech\asstAPI;


use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;
//use Defuse\Crypto\KeyProtectedByPassword;
//use Defuse\Crypto\Exception as Ex;
//use Defuse\Crypto\File;


/**
 * HelixTech\asstAPI\loadEncryptionKeyFromConfig - retrieve the stored encryption key 
 * @return mixed
 */
function loadEncryptionKeyFromConfig()
{
    $keyfile = parse_ini_file(realpath('/var/www/private/keyfile.ini'));
	$key = $keyfile['KEY'];

	return Key::loadFromAsciiSafeString($key);
}

/**
 * HelixTech\asstAPI\encrypt: return ciphertext given plaintext
 * @param mixed $input 
 * @return mixed
 */
function encrypt($input){

	$key = loadEncryptionKeyFromConfig();
	$ciphertext = Crypto::encrypt($input, $key);
	return $ciphertext;
}

/**
 * Summary of HelixTech\asstAPI\decrypt: return plaintext from ciphertext
 * @param mixed $input 
 * @return mixed
 */
function decrypt($input){
	$key = loadEncryptionKeyFromConfig();
	try
	{
		$plaintext = Crypto::decrypt($input, $key);

	} catch (\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
		// An attack! Either the wrong key was loaded, or the ciphertext has
		// changed since it was created -- either corrupted in the database or
		// intentionally modified by Eve trying to carry out an attack.

		// ... handle this case in a way that's suitable to your application ...
		Output::errorMsg("caught exception: "."Wrong Key Or Modified Ciphertext Exception Thrown -  ".$ex."\n");
	}


	return $plaintext;

}



?>