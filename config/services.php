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

    'sidooh' => [
        'jwt_key' => env('JWT_KEY'),
        'tagline' => 'Sidooh, Makes You Money with Every Purchase.',
        'mpesa' => [
            'env' => 'local',
            'b2c' => [
                'phone' => '254708374149',
                'min_amount' => '10',
                'max_amount' => '70000',
            ],
        ],
        'country_code' => env('COUNTRY_CODE', 'KE'),
        'services' => [
            'notify' => [
                'enabled' => true,
                'url' => env('SIDOOH_NOTIFY_API_URL'),
            ],
            'accounts' => [
                'enabled' => true,
                'url' => env('SIDOOH_ACCOUNTS_API_URL'),
            ],
            'products' => [
                'enabled' => true,
                'url' => env('SIDOOH_PRODUCTS_API_URL'),
            ],
            'savings' => [
                'enabled' => true,
                'url' => env('SIDOOH_SAVINGS_API_URL'),
            ],
        ],
    ],

];
