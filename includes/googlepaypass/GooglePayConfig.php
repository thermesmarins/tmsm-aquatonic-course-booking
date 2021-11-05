<?php

$pluginoptions = get_option( 'tmsm-aquatonic-course-booking' . '-options' );

// Identifiers of Service account
define('SERVICE_ACCOUNT_EMAIL_ADDRESS', $pluginoptions['accountemail']);  //CHANGEME
define('SERVICE_ACCOUNT_FILE', '/app/aquatonic-5c6c665f8318.json');  //CHANGEME

// Used by the Google Pay API for Passes Client library
define('APPLICATION_NAME', $pluginoptions['applicationname']); //CHANGEME

// Identifier of Google Pay API for Passes Merchant Center
define('ISSUER_ID', $pluginoptions['issuerid']);  //CHANGEME

// Origin
$urlparts = parse_url(home_url());
$domain = $urlparts['host'];
$scheme = $urlparts['scheme'];
define('ORIGINS', $scheme . '://' . $domain);  //CHANGEME

// Constants that are application agnostic. Used for JWT
define('AUDIENCE', 'google');
define('JWT_TYPE', 'savetoandroidpay');
define('SCOPES', 'https://www.googleapis.com/auth/wallet_object.issuer');

// Load the private key as String from service account file
$jsonFile = file_get_contents(SERVICE_ACCOUNT_FILE);
$credentialJson = json_decode($jsonFile, true);
define('SERVICE_ACCOUNT_PRIVATE_KEY',$credentialJson['private_key']);