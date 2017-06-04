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


$filename = 'https://who.is/whois-ip/ip-address/40.77.167.135';
$whois = file_get_contents($filename);
$start = strpos($whois, '<div class="col-md-12 queryResponseBodyKey"');
$end = strpos($whois, '</pre>', $start);
$whois = substr($whois, $start+49, $end-$start-49);
$whois = explode("\n", $whois);
$whoisAssoc = Array();
foreach ($whois as $who){
var_dump($who);
	$temp = explode(":",$who);
	$whoType = $temp[0];
	$who = $temp[1];
	$whoisAssoc[$whoType] = $who;
}
var_dump($whois);
var_dump($whoisAssoc);
//echo $whois['NetName'].", ".$whois['Organization'];

use HelixTech\asstAPI\{Connection, Router, Output};





Connection::connect();
Router::route();
Output::go();

?>