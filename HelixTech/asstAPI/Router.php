<?php namespace HelixTech\asstAPI;
/**
 * @author Philip Alton
 * @copyright Helix Tech Ltd. 2017
 * @file Router.php
 * @package asstAPI
 *
 * @todo re-write class with new notation as document at bottom of class
 */


use HelixTech\asstAPI\{Connection};
use HelixTech\asstAPI\Models\{Data, User};
use HelixTech\asstAPI\Exceptions\{InvalidURI, ConnectionFailed};

/**
* Summery of Router: class to map URL Endpoints to functions
*
*/
class Router{


    public static function route(){

        $method = Connection::getMethod();
        $request = Connection::getRequest();
        $input = Connection::getInput();



        // Switch to govern action based on URI
        try{
            if (!Connection::isEstablished()){throw new ConnectionFailed;}
            if (Router::uri('asst/Users/..*') and ($request[2]==Connection::getUserName()))       // ensure that user specific end points are only accesible
            {
                $UserName = $request[2];
                if (isset($request[3]))
                {
                    switch($request[3])
                    {
                        case "sync":
                            // case for /asst/Users/Id/Data      "/asst/Users/$UserName/sync"
                            Data::syncData($method, $input);
                            break;
                        case "resetPassword":
                            //case for restetting password  "/asst/Users/$UserName/resetPassword"
                            User::resetPassword($UserName);
                            break;
                        default: throw new InvalidURI("Invalid URI selected".Connection::getURI());
                    }

                } else {
                    // action for /asst/Users/Id
                    User::handleRequest($method, $UserName, $input);
                }

            } elseif (Router::uri('asst/Users')){
                // code for asst/Users (create new user)
                User::createUser($input);
            } else {
                throw new InvalidURI("Invalid URI selected".Connection::getURI());
            }


        } catch (InvalidURI $e) {
            http_response_code(404);
            Output::errorMsg("caught exception: ".$e->getMessage().".");
        }
        catch (ConnectionFailed $e) {
            Output::errorMsg("Connection Failed: request terminated");
        }



    }


    private static function uri($match){
        $match = "#".$match."#";
        return preg_match($match, Connection::getURI());

    }





    /**
    * @code
    *
    * in index.php:
    *    MapToEndpoint(VERB, URL)
    *
    * in Router.php:
    *
    *    Public Static Function MapToEndpoint($method, $url){
    *        for the first entity in the url (i.e. between / /){
    *
    *            Entity::Route($method, rest of url);
    *
    *    }
    *
    *
    * in entity class (eg User class)
    *    Public Static function Route($method, $rest of url ){
    *        Select
    *        case 2nd entity = x
    *        case 2nd entity = y
    *        default: 2ndEntity::Route($method, $url further redacted)
    * }
    *
    * @endcode
    *
    */




}


?>