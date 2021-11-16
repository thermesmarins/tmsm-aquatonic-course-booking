<?php

$pluginoptions = get_option( 'tmsm-aquatonic-course-booking' . '-options' );

if(
	! empty( $pluginoptions['googlepaypasses_accountemail'] )
	&& ! empty( $pluginoptions['googlepaypasses_accountfilepath'] )
	&& ! empty( $pluginoptions['googlepaypasses_applicationname'] )
	&& ! empty( $pluginoptions['googlepaypasses_issuerid'] )
){
	// Identifiers of Service account
	define('SERVICE_ACCOUNT_EMAIL_ADDRESS', $pluginoptions['googlepaypasses_accountemail']);
	define('SERVICE_ACCOUNT_FILE', $pluginoptions['googlepaypasses_accountfilepath']);

	// Used by the Google Pay API for Passes Client library
	define('APPLICATION_NAME', $pluginoptions['googlepaypasses_applicationname']);

	// Identifier of Google Pay API for Passes Merchant Center
	define('ISSUER_ID', $pluginoptions['googlepaypasses_issuerid']);

	// Origin
	$urlparts = parse_url(home_url());
	$domain = $urlparts['host'];
	$scheme = $urlparts['scheme'];
	define('ORIGINS', $scheme . '://' . $domain);

	// Constants that are application agnostic. Used for JWT
	define('AUDIENCE', 'google');
	define('JWT_TYPE', 'savetoandroidpay');
	define('SCOPES', 'https://www.googleapis.com/auth/wallet_object.issuer');

	// Load the private key as String from service account file
	$jsonFile = file_get_contents(SERVICE_ACCOUNT_FILE);
	$credentialJson = json_decode($jsonFile, true);
	define('SERVICE_ACCOUNT_PRIVATE_KEY',$credentialJson['private_key']);
}
