<?php
    $webContactName = $_GET["webContactName"];
    $webContactEmail = $_GET["webContactEmail"];
    $webContactSubject = $_GET["webContactSubject"];
    $webContactMessage = $_GET["webContactMessage"];


    // Send an email to the user containing the unique link
    $message = "Message From: " . $webContactName . "\r\n"
    . "Email: " . $webContactEmail . "\r\n\r\n"
    . "Subject: " . $webContactSubject . "\r\n"
    . $webContactMessage;

    $headers = 'From: CRVW@axspa.org.uk' . "\r\n" .
    'Reply-To: '.$webContactEmail;

    mail('inquire@helix-tech.co.uk', 'Ankylosing Spondylitis Symptom Tracker - Contact Request via Website', $message, $headers);


    $output = "<!DOCTYPE html>"
    ."<html>"
    .    '<meta charset="UTF-8"/>'
    .    "<title>Contact Email</title>"
    ."</head>"
    .
    ."<body>"
    .    '<a href="#home" class="w3-bar-item w3-button w3-wide logo"><img src="/asst/HelixTech/Public/asstAPI/Images/logo.png" style="max-height:80px;max-width:450px;padding:0;margin:0;"></a>'
    .    "<div><a href='https://testing.axspa.org.uk'>Email sent - we'll be in Touch!</a></div>"
    ."</body>"
    ."</html>";

    echo $output;

?>

