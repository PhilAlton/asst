<?php
echo "some things";


// Send an email to the user containing the unique link
$message = 'Please click the following link to reset your password:' . "\r\n"
."https://axspa.org.uk/passwordReset.html?".urlencode("username=".$UserName."&GUIDE=".$uniqueCode) . "\r\n\r\n"
//		."debug: uniqueID=" . $uniqueID . "\r\n\r\n"
//		."debug: PassResTokEx=" . $expiary . "\r\n\r\n"
. "Please note, this link will expire in 12 hours";

$headers = 'From: ResetPassword@axspa.org.uk' . "\r\n" .
'Reply-To: ResetPassword@axspa.org.uk';

mail($UserName, 'Ankylosing Spondylitis Symptom Tracker - Request to Reset Password', $message, $headers);

?>

