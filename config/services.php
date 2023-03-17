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
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sidooh' => [
        'jwt_key'      => env('JWT_KEY'),
        'tagline'      => 'Sidooh, Makes You Money with Every Purchase.',
        'mpesa'        => [
            'env' => 'local',
            'b2c' => [
                'phone'      => '254708374149',
                'min_amount' => '10',
                'max_amount' => '70000',
            ],
        ],
        'country_code' => env('COUNTRY_CODE', 'KE'),
        'services'     => [
            'notify'   => [
                'enabled' => true,
                'url'     => env('SIDOOH_NOTIFY_API_URL'),
            ],
            'accounts' => [
                'enabled' => true,
                'url'     => env('SIDOOH_ACCOUNTS_API_URL'),
            ],
            'products' => [
                'enabled' => true,
                'url'     => env('SIDOOH_PRODUCTS_API_URL'),
            ],
            'savings'  => [
                'enabled' => true,
                'url'     => env('SIDOOH_SAVINGS_API_URL'),
            ],
        ],
        'charges'      => [
            'withdrawal' => [
                ['min' => 50, 'max' => 1000, 'charge' => 50],
                ['min' => 1000, 'max' => 10000, 'charge' => 100],
            ],
            'paybill'    => [
                ['min' => 1, 'max' => 100, 'charge' => 0],
                ['min' => 101, 'max' => 500, 'charge' => 4],
                ['min' => 501, 'max' => 500, 'charge' => 9],
                ['min' => 1001, 'max' => 1500, 'charge' => 14],
                ['min' => 1501, 'max' => 2500, 'charge' => 19],
                ['min' => 2501, 'max' => 3500, 'charge' => 24],
                ['min' => 3501, 'max' => 5000, 'charge' => 33],
                ['min' => 5001, 'max' => 7500, 'charge' => 40],
                ['min' => 7501, 'max' => 10000, 'charge' => 46],
                ['min' => 10001, 'max' => 15000, 'charge' => 55],
                ['min' => 15001, 'max' => 20000, 'charge' => 60],
                ['min' => 20001, 'max' => 25000, 'charge' => 65],
                ['min' => 25001, 'max' => 30000, 'charge' => 70],
                ['min' => 30001, 'max' => 35000, 'charge' => 80],
                ['min' => 35001, 'max' => 40000, 'charge' => 96],
                ['min' => 40001, 'max' => 45000, 'charge' => 100],
                ['min' => 45001, 'max' => 150000, 'charge' => 105],
            ],
        ],
        'payment_providers' => [
            'mpesa' => [
                'paybill_switch_amount' => env('SIDOOH_PAYBILL_SWITCH_AMOUNT'),
                'paybill' => [
                    'key' => env('SIDOOH_PAYBILL_KEY'),
                    'secret' => env('SIDOOH_PAYBILL_SECRET'),
                    'passkey' => env('SIDOOH_PAYBILL_PASS_KEY'),
                    'shortcode' => env('SIDOOH_PAYBILL_SHORTCODE'),
                ],
            ]
        ]
    ],
];
