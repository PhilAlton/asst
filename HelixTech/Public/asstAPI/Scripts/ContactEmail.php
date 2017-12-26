<?php
echo "some other things";

$webContactName = $_GET["webContactName"];
$webContactEmail = $_GET["webContactEmail"];
$webContactSubject = $_GET["webContactSubject"];
$webContactMessage = $_GET["webContactMessage"];


// Send an email to the user containing the unique link
$message = "Message From: " . $webContactName . "(email: " . $webContactEmail . ")" . "\r\n\r\n"
. "Subject: " . $webContactSubject . "\r\n"
. $webContactMessage;

$headers = 'From: CRVW@axspa.org.uk' . "\r\n" .
'Reply-To: '.$webContactEmail;

mail('inquire@helix-tech.co.uk', 'Ankylosing Spondylitis Symptom Tracker - Contact Request via Website', $message, $headers);

?>

