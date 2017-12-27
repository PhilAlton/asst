<?php
    $webContactName = $_GET["webContactName"];
    $webContactEmail = $_GET["webContactEmail"];
    $webContactSubject = $_GET["webContactSubject"];
    $webContactMessage = $_GET["webContactMessage"];


    // Send an email to the Helix-Tech inquire account with the details of the user's message
    $message = "Message From: " . $webContactName . "\r\n"
    . "Email: " . $webContactEmail . "\r\n\r\n"
    . "Subject: " . $webContactSubject . "\r\n"
    . $webContactMessage;

    $headers = 'From: CRVW@axspa.org.uk' . "\r\n" .
    'Reply-To: '.$webContactEmail;

    mail('inquire@helix-tech.co.uk', 'Ankylosing Spondylitis Symptom Tracker - Contact Request via Website', $message, $headers);

    // Output a response page indicating success.
    $output = "<!DOCTYPE html>"
    ."<html>"
    .    '<meta charset="UTF-8"/>'
    .    "<title>Contact Email</title>"
    .    "<style>"
    .        'body,h1,h2,h3,h4,h5 {font-family: "Poppins", sans-serif}'
    .        "body {font-size: 16px;}"
    .        "img {margin-bottom: -8px;}"
    .        ".mySlides {display: none;}"
    .    "</style>"
    ."</head>"
    ."<body>"
    ."<div style='max-width:500px;margin:auto;'>"
    .    '<a href="#home" class="w3-bar-item w3-button w3-wide logo"><img src="/asst/HelixTech/Public/asstAPI/Images/logo.png" style="max-height:80px;max-width:450px;padding:0;margin:0;"></a>'
    .    "<div><a href='https://testing.axspa.org.uk'>Email sent - we'll be in Touch!</a></div>"
    ."<div>"
    ."</body>"
    ."</html>";

    echo $output;

?>

