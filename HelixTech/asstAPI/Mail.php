<?php

/** SMTP mail */

define('SENDER', 'donotreply@axspa.org.uk');
define('RECIPIENT', 'phil.alton@helix-tech.co.uk');
define('USERNAME','AKIAIO7NXLRMD7RKTPPQ');
define('PASSWORD','AsRrAs+h99hfVDxAXGUdZ/iH+TqymHlTGhFD8eKf4UMv');

define('HOST', 'email-smtp.eu-west-1.amazonaws.com');

define('PORT', '587');

// message information
define('SUBJECT','Amazon SES test (SMTP interface accessed using PHP)');
define('BODY','This email was sent through the Amazon SES SMTP interface by using PHP.');

require_once 'Mail.php';
$headers = array (
  'From' => SENDER,
  'To' => RECIPIENT,
  'Subject' => SUBJECT);

$smtpParams = array (
  'host' => HOST,
  'port' => PORT,
  'auth' => true,
  'username' => USERNAME,
  'password' => PASSWORD
);

// Create an SMTP client.
$mail = Mail::factory('smtp', $smtpParams);

// Send the email.
$result = $mail->send(RECIPIENT, $headers, BODY);

if (PEAR::isError($result)) {
    echo("Email not sent. " .$result->getMessage() ."\n");
} else {
    echo("Email sent!"."\n");
}



/** php mail */

$to = 'phil.alton@gmail.com';
$subject = 'Test PHP Mail';
$message = 'Hello Phil';
$headers = 'From: webmaster@axspa.org.uk' . "\r\n" .
            'Reply-To: webmaster@axspa.org.uk';

mail($to, $subject, $message, $headers);


?>