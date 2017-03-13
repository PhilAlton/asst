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
require_once 'analytics.php';

use HelixTech\asstAPI\{Connection, Router, Output};

$array = array();
array_push($array, "this");
array_push($array, "more");
var_dump($array);

Connection::connect();
Router::route();
Output::go();

?>