<?php
require_once '../vendor/autoload.php';

use Defuse\Crypto\Exception as Ex;
use Defuse\Crypto\File;
use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\KeyProtectedByPassword;




function loadEncryptionKeyFromConfig()
{
    $keyfile = parse_ini_file(realpath('../../../private/keyfile.ini'));
	$key = $keyfile['KEY'];

	return Key::loadFromAsciiSafeString($key);
}


function encrypt_input($input){

	$key = loadEncryptionKeyFromConfig();

	var_dump($input);
	//	$ciphertext = Crypto::encrypt($secret_data, $key);


	return $input;
}

function decrypt_output($output){

	$key = loadEncryptionKeyFromConfig();

	$ciphertext = "data";// ... load $ciphertext from the database
	try
	{
		$secret_data = Crypto::decrypt($ciphertext, $key);

	} catch (\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
		// An attack! Either the wrong key was loaded, or the ciphertext has
		// changed since it was created -- either corrupted in the database or
		// intentionally modified by Eve trying to carry out an attack.

		// ... handle this case in a way that's suitable to your application ...
		Output::setOutput("caught exception: ".""."\n");
	}


	return $output;

}



?>