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

require_once dirname(dirname(__FILE__)) . '/../bootstrap.php';

use HelixTech\asstAPI\{Connection, Router, Output};

try{    // Exceptions handled at lower levels
    Connection::connect();
    } 

catch (Exception $e){    
    die("dying");
}

    Router::route();
    Output::go();

?>