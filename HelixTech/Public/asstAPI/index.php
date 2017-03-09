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

echo json_decode('{"1":"one", "2":"two"}{"3":"three"}');

Connection::connect();
Router::route();
Output::go();

?>