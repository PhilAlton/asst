<?php namespace HelixTech\asstAPI;

use HelixTech\asstAPI\{Connection, Crypt};
use HelixTech\asstAPI\Models\User;

Class Auth{

    public static function verifyPassword($password){
        $q_auth = false;
        // Check if the hash of the entered login password, matches the stored hash.
        Auth::authentic(password_verify(
            base64_encode(hash('sha384', Connection::getPassword(), true)),
            $password
        ));
        return $q_auth;

    }


    public static function verifyGoogleID($payload){
        $q_auth = false;
        if ($payload) {
            $userid = $payload['sub'];
            var_dump($payload);  
            Auth::authentic(true);
        } else {
            Auth::authentic(false);
        }
        return $q_auth;
    
    }

    
    private static function authentic($auth){
        if ($auth) {
            User::$uID = Connection::getUserName();
            Connection::authentic();
            $q_auth = true;
        } else {
            Connection::notAuthentic();
            $q_auth = false;
        }

    }



}





/*
http_response_code(100); // N Continue (send POST body)
http_response_code(417); // Expectation failed (i.e. don't send POST)
http_response_code(302); // Found
http_response_code(307); // Temporary redirect (repeat request to another URI, still use this URI for future requests)

http_response_code(200); // Y OK
http_response_code(201); // Y Created
http_response_code(204); // Y No content *(request fulfilled)


http_response_code(401); // not authorised
http_response_code(403); // Forbidden - i.e logged on but not authorised for particular content
http_response_code(404); // not found
http_response_code(406); // input not acceptable
http_response_code(409); // Conflict


header("HTTP/1.0 418 I'm A Teapot");
*/


?>