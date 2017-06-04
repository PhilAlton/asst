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
$handle = fopen ('https://who.is/whois-ip/ip-address/40.77.167.135',"r");
echo fread($handle,EOF);
use HelixTech\asstAPI\{Connection, Router, Output};





Connection::connect();
Router::route();
Output::go();

?>