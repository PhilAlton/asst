<?php namespace HelixTech\asstAPI;
/**
 * @author Philip Alton
 * @copyright Helix Tech Ltd. 2017
 * @file Router.php
 * @package asstAPI
 *
 * @todo re-write class with new notation as document at bottom of class
 */


use HelixTech\asstAPI\{Connection, Paginate};
use HelixTech\asstAPI\Models\{Data, User, Analytics};
use HelixTech\asstAPI\Exceptions\{InvalidURI, ConnectionFailed, AttemptedToAccessUnauthorisedResources};

/**
* Summery of Router: class to map URL Endpoints to functions
*
*/
class Router{

	
    public static function route(){
		set_error_handler("exception_error_handler");

        // Switch to govern action based on URI
        try{
            if (!Connection::isEstablished()){throw new ConnectionFailed;}

            $method = Connection::getMethod();
            $request = Connection::getRequest();
            $input = Connection::getInput();
            $root = Connection::getAPIroot();


            if (Router::uri($root.'/Users/..*'))       // ensure that user specific end points are only accesible
            {
			    if (isset($request[1]) and $request[1]<>Connection::getUserName()){
					throw new AttemptedToAccessUnauthorisedResources;
				}
                $UserName = $request[1];
                if (isset($request[2]))
                {
                    switch($request[2])
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

            } elseif (Router::uri($root.'/Users')){
                // code for asst/Users (create new user)
                User::createUser($input);

			} elseif (Router::uri($root.'/Cache')){
                // code for asst/Cache (withdraw paginated data)
				//@todo: need to validate user!
				$cachefile = $request[1];
				$UserName = explode('-asstAPIcache-',$cachefile)[0];
				Paginate::retrieve($UserName, $cachefile);

            } elseif (Router::uri($root.'/passwordReset')){
                // proceed with password reset
				User::passwordReset();

            } elseif (Router::uri($root.'/Analytics')){
                // code for asst/Analytics
                Analytics::display();

            } else {
                throw new InvalidURI("Invalid URI selected".Connection::getURI());
            }

			
        } catch (InvalidURI $e) {
            http_response_code(404);
            Output::errorMsg("caught exception: ".$e->getMessage().".");
        } catch (ConnectionFailed $e) {
            Output::errorMsg("Connection Failed: request terminated");
        } catch (AttemptedToAccessUnauthorisedResources $e){
            Output::errorMsg("User details do not match requested resources");
        } catch (\Exception $e) {
			http_response_code(404);
			Output::errorMsg("Other Error Thrown: ".$e->getMessage());
			Output::setOutput('Error: '.$e->getMessage());
		}

		restore_error_handler();

    }


    private static function uri($match){
        $match = "#".$match."#";
        return preg_match($match, Connection::getURI());

    }



    /**
    * @code
    *
    * in index.php:
    *    $connection = new Connection();
    *    $route = new Route();
    *    $route->toEndpoint($connection->getVerb(), $connection->getMethod())
    *
    *
    * in Router.php:
    *
    *    Public Function toEndpoint($method, $url){
    *        for the first entity in the url (i.e. between / /){
    *       e.g. $entity = array_shift(&$url)
    *
    *            Entity::Route($method, &$url);
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