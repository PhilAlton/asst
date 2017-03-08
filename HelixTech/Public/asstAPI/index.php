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


$to = 'phil.alton@gmail.com';
$subject = 'Test PHP Mail';
$message = 'Hello Phil';
$headers = 'From: webmaster@axspa.org.uk' . "\r\n" .
            'Reply-To: webmaster@axspa.org.uk' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);


Connection::connect();
Router::route();
Output::go();

?>