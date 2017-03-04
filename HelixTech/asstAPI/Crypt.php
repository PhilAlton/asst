<?php namespace HelixTech\asstAPI;
/**
 * Crypt Class
 * Uses the Defuse\Crypto library for safe encryption
 */


use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;
//use Defuse\Crypto\KeyProtectedByPassword;
//use Defuse\Crypto\Exception as Ex;
//use Defuse\Crypto\File;


/**
 * Summary of Crypt: Class containing functions related to the encryption and decryption of data
 */
class Crypt{


    /**
     * HelixTech\asstAPI\UseEncryptionKey - retrieve the stored encryption key, use it in the specified function
     * Clear the key afterwards (reduces key exposure)
     *
     * @param $callBackFunction -
     * @param $args (variable) - args to be passed to the function
     *
     * @todo fix arguments passed to callback
     *      (currently will not work if the order of args into the callback isn't as below)
     *
     * @return mixed
     */
    private static function UseEncryptionKey(callable $callBackFunction, ...$args){
        // retrieve key
        $private_PATH = ($_SERVER['REMOTE_ADDR'] == "::1" ? 'C:\xampp\htdocs\private\asst\\' : realpath('/var/www/private/'));

        // Find the index of the argument passed as null
        // (this implies, to this function, that the argument in question should be updated with the encryption key)
        $indexOfNullArg = (array_search(null, $args));

        /** @param $args[$indexOfNullArg] modified: with encrpytion key */
        $args[$indexOfNullArg] = Key::loadFromAsciiSafeString(parse_ini_file($private_PATH.'keyfile.ini')['KEY']);
       
        // call the function deploying the key, with its other arguments as an array
        // set the return value of the fucntion, so that the return value can bubble up
        $return = call_user_func_array($callBackFunction, $args);
        
        // Store a random string of bytes in the key index, in order to remove the index
        $args[$indexOfNullArg] = random_bytes(102);
        // Then set the index to null in order to free up the memory to further protect the encryption key
        $args[$indexOfNullArg] = null;
        
        return $return;
    }



    /**
     * Summary of HelixTech\asstAPI\encrypt: return ciphertext given plaintext
     * @param mixed $input plaintext
     * @return mixed cyphertext
     */
    public static function encrypt($input){
                                                  // syntax here maps to synatx of encrypt();
        return Crypt::UseEncryptionKey("Defuse\Crypto\Crypto::encrypt", $input, $key = null);

    }


    /**
     * Summary of HelixTech\asstAPI\decrypt: return plaintext from ciphertext
     * @param mixed $input cyphertext
     * @throws \Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException if the data has become corrupted
     * @return mixed plaintext
     */
    public static function decrypt($input){

        try {
            // call Crypto::decrypt via UseEncryptionKey, mapping that functions argument syntax
            $plaintext = Crypt::UseEncryptionKey("Defuse\Crypto\Crypto::decrypt", $input, $key = null);

	    } catch (\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
		    // An attack! Either the wrong key was loaded, or the ciphertext has
		    // changed since it was created -- either corrupted in the database or
		    // intentionally modified by someone trying to carry out an attack.

		    // ... handle this case
            Output::errorMsg("caught exception: "."Wrong Key Or Modified Ciphertext Exception Thrown -  ".$ex."\n");
            /** @todo need to set up logging for corrupted data */

	    }

        return $plaintext;
    }
}

?>