<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'soap' => [
        'ClientInfo' => [
            'AccountCountryCode'	=> env('ACCOUNT_COUNTRY_CODE'),
            'AccountEntity'		 	=> env('ACCOUNT_ENTITY'),
            'AccountNumber'		 	=> env('ACCOUNT_NUMBER'),
            'AccountPin'		 	=> env('ACCOUNT_PIN'),
            'UserName'			    => env('USER_NAME'),
            'Password'		 	    => env('PASSWORD'),
            'Version'		 	    => env('VERSION'),
            'Source' 			    => env('SOURCE'),
        ],
        'location_wsdl' => public_path().'/storage/aramex_keys/Location-API -WSDL.wsdl',
        'rates_wsdl' => public_path().'/storage/aramex_keys/aramex-rates-calculator-wsdl.wsdl',
        'tracking_wsdl' => public_path().'/storage/aramex_keys/shipments-tracking-api-wsdl.wsdl',
        'shipping_wsdl' => public_path().'/storage/aramex_keys/shipping-services-api-wsdl.wsdl',
    ],
];
