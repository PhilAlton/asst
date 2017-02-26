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


function encrypt($input){
	$input = base64_encode($input);
	$key = loadEncryptionKeyFromConfig();
	$ciphertext = Crypto::encrypt($input, $key);
	return $ciphertext;
}

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
		Output::setOutput("caught exception: "."Wrong Key Or Modified Ciphertext Exception Thrown"."\n");
	}

	$plaintext = base64_decode($plaintext);
	return $plaintext;

}



?>