<?php
/**
 * Landing point
 * Establish safe connection
 * Route URL and map to appropriate function
 * Process output

 *
 * @author Philip Alton
 * @copyright Helix Tech Ltd. 2017
 * @file index.php
 * @package asstAPI
 *
 *
 */


 //TODO: Params class would be really helpful in order to ensure, for each request, the suitable params have been called
 //Basically, for each $param in the post / get, this causes the creation of a member variale in the params class
 //When any of these member variables are acessed 
 //     (i.e rather than $params['someSpecificParam'], you would be accesing params->get('someSpecificParam'))
 //This would allow any index out of bounds errors, which are specifically post/get in origin, to be caught
 //Allowing the API to inform the client that some get or post parameter has not been sent
 

require_once dirname(dirname(__FILE__)) . '/../bootstrap.php';
require_once 'analytics.php';

use HelixTech\asstAPI\{Connection, Router, Output};

// Override default error handler to convert notices to errors
function exception_error_handler($severity, $message, $file, $line){
	if (!(error_reporting() & $severity)){
		// This error code is not included in error_reporting
		return;
	} 
	throw new ErrorException($message, 0, $severity, $file, $line);
}


Connection::connect();
Router::route();
Output::go();

?>