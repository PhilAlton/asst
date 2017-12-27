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
    ."<head>"
    .   '<meta charset="UTF-8"/>'
    .   "<title>Contact Email</title>"
	.   '<meta name="viewport" content="width=device-width, initial-scale=1">'
	.   '<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">'
	.   '<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">'
	.   '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">'
    .    "<style>"
    .        'body,h1,h2,h3,h4,h5 {font-family: "Poppins", sans-serif}'
    .        "body {font-size: 16px;}"
    .        "img {margin-bottom: -8px;}"
    .        ".mySlides {display: none;}"
    .    "</style>"
    ."</head>"
    
    ."<body>"
    .    '<div class="w3-card-4 w3-hover-shadow w3-center" style="max-width:500px;margin:auto;margin-top:25%">'
    .    '<header class="w3-container">'
    .    '<a href="#home" class="w3-bar-item w3-btn w3-wide logo"><img src="/asst/HelixTech/Public/asstAPI/Images/logo.png" style="max-height:80px;max-width:450px;padding:0;margin:0;"></a>'
    .    '</header>'
    .    '<div class="w3-container w3-light-grey">'
    .    "<div class='w3-xxxlarge'><a href='https://testing.axspa.org.uk'>Email sent - <br/>we'll be in Touch!</a></div>"
    .    '</div>'
    .    '<button class="w3-button w3-block w3-dark-grey" onclick="document.getElementById(\'download\').style.display=\'block\'">Download <i class="fa fa-android"></i> <i class="fa fa-apple"></i> <!--<i class="fa fa-windows"></i>--></button>'
    .    '</div>'
 //   ."<div class='w3-card-4 w3-dark-grey' style='max-width:500px;margin:auto;'>"
 //   .    '<a href="#home" class="w3-bar-item w3-button w3-wide logo"><img src="/asst/HelixTech/Public/asstAPI/Images/logo.png" style="max-height:80px;max-width:450px;padding:0;margin:0;"></a>'
 //   .    "<div class='w3-xxxlarge'><a href='https://testing.axspa.org.uk'>Email sent - we'll be in Touch!</a></div>"
 //   ."<div>"
    .    "<!-- Modal -->"
    .    '<div id="download" class="w3-modal w3-light-grey w3-animate-opacity">'
    .    '<div class="w3-modal-content" style="padding:32px">'
    .        '<div class="w3-container w3-white">'
    .        '<i onclick="document.getElementById(\'download\').style.display=\'none\'" class="fa fa-remove w3-xlarge w3-button w3-transparent w3-right w3-xlarge"></i>'
    .        '<h2 class="w3-wide">DOWNLOAD</h2>'
    .        '<p>Download the app in AppStore or Google Play store.</p>'
    .        '<i class="fa fa-android w3-large"></i> <i class="fa fa-apple w3-large"></i><!--<i class="fa fa-windows w3-large"></i>-->'
    .        '<p><input class="w3-input w3-border" type="text" placeholder="Enter e-mail"></p>'
    .        '<button type="button" class="w3-button w3-block w3-padding-large w3-red w3-margin-bottom" onclick="downloadApp()">Download</button>'
    .        '</div>'
    .    '</div>'
    .    '</div>'

    .    "<script>"
    .    "function downloadApp(){"
    .        "if(navigator.userAgent.toLowerCase().indexOf('android') > -1){"
    .            "window.location.href = 'https://play.google.com/store/apps/details?id=com.ankspondtracker.android.asapp';"
    .        "}"
    .        "if(navigator.userAgent.toLowerCase().indexOf('iphone') > -1){"
    .            "window.location.href = 'https://play.google.com/store/apps/details?id=com.ankspondtracker.android.asapp';"
    .        "}"
    .    "}"
    .    "</script>"

    ."</body>"
    ."</html>";

    echo $output;

?>

