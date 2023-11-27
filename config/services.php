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
        'jwt_key'           => env('JWT_KEY'),
        'tagline'           => 'Sidooh, Makes You Money with Every Purchase.',
        'mpesa'             => [
            'env' => 'local',
            'b2c' => [
                'phone'      => '254708374149',
                'min_amount' => '10',
                'max_amount' => '70000',
            ],
        ],
        'country_code'      => env('COUNTRY_CODE', 'KE'),
        'services'          => [
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
        'charges'           => [
            'mpesa_float' => [
                ['min' => 1, 'max' => 100000, 'charge' => 30],
                ['min' => 100001, 'max' => 400000, 'charge' => 30],
            ],
            'mpesa_withdrawal' => [
                ['min' => 50, 'max' => 100, 'charge' => 11],
                ['min' => 101, 'max' => 2500, 'charge' => 29],
                ['min' => 2501, 'max' => 3500, 'charge' => 52],
                ['min' => 3501, 'max' => 5000, 'charge' => 69],
                ['min' => 5001, 'max' => 7500, 'charge' => 87],
                ['min' => 7501, 'max' => 10000, 'charge' => 115],
                ['min' => 10001, 'max' => 15000, 'charge' => 167],
                ['min' => 15001, 'max' => 20000, 'charge' => 185],
                ['min' => 20001, 'max' => 35000, 'charge' => 197],
                ['min' => 35001, 'max' => 50000, 'charge' => 278],
                ['min' => 50001, 'max' => 150000, 'charge' => 309],
            ],
//            'mpesa_withdrawal' => [
//                ['min' => 50, 'max' => 100, 'charge' => 5],
//                ['min' => 101, 'max' => 1500, 'charge' => 13],
//                ['min' => 1501, 'max' => 2500, 'charge' => 10],
//                ['min' => 2501, 'max' => 3500, 'charge' => 23],
//                ['min' => 3501, 'max' => 5000, 'charge' => 30],
//                ['min' => 5001, 'max' => 7500, 'charge' => 35],
//                ['min' => 7501, 'max' => 10000, 'charge' => 40],
//                ['min' => 10001, 'max' => 15000, 'charge' => 73],
//                ['min' => 15001, 'max' => 20000, 'charge' => 80],
//                ['min' => 20001, 'max' => 35000, 'charge' => 86],
//                ['min' => 35001, 'max' => 50000, 'charge' => 121],
//                ['min' => 50001, 'max' => 150000, 'charge' => 133],
//            ],
            'withdrawal' => [
                ['min' => 1, 'max' => 100, 'charge' => 3],
                ['min' => 101, 'max' => 1500, 'charge' => 15],
                ['min' => 1501, 'max' => 5000, 'charge' => 20],
                ['min' => 5001, 'max' => 250000, 'charge' => 50],
            ],
            'buy_goods'  => [
                ['min' => 1, 'max' => 49, 'charge' => 2],
                ['min' => 50, 'max' => 100, 'charge' => 3],
                ['min' => 101, 'max' => 500, 'charge' => 5],
                ['min' => 501, 'max' => 1000, 'charge' => 10],
                ['min' => 1001, 'max' => 1500, 'charge' => 15],
                ['min' => 1501, 'max' => 2500, 'charge' => 20],
                ['min' => 2501, 'max' => 3500, 'charge' => 25],
                ['min' => 3501, 'max' => 5000, 'charge' => 34],
                ['min' => 5001, 'max' => 7500, 'charge' => 42],
                ['min' => 7501, 'max' => 10000, 'charge' => 48],
                ['min' => 10001, 'max' => 15000, 'charge' => 57],
                ['min' => 15001, 'max' => 20000, 'charge' => 62],
                ['min' => 20001, 'max' => 25000, 'charge' => 67],
                ['min' => 25001, 'max' => 30000, 'charge' => 72],
                ['min' => 30001, 'max' => 35000, 'charge' => 83],
                ['min' => 35001, 'max' => 40000, 'charge' => 99],
                ['min' => 40001, 'max' => 45000, 'charge' => 103],
                ['min' => 45001, 'max' => 250000, 'charge' => 108],
            ],
            'pay_bill'   => [
                ['min' => 1, 'max' => 49, 'charge' => 2],
                ['min' => 50, 'max' => 100, 'charge' => 3],
                ['min' => 101, 'max' => 500, 'charge' => 5],
                ['min' => 501, 'max' => 1000, 'charge' => 10],
                ['min' => 1001, 'max' => 1500, 'charge' => 15],
                ['min' => 1501, 'max' => 2500, 'charge' => 20],
                ['min' => 2501, 'max' => 3500, 'charge' => 25],
                ['min' => 3501, 'max' => 5000, 'charge' => 34],
                ['min' => 5001, 'max' => 7500, 'charge' => 42],
                ['min' => 7501, 'max' => 10000, 'charge' => 48],
                ['min' => 10001, 'max' => 15000, 'charge' => 57],
                ['min' => 15001, 'max' => 20000, 'charge' => 62],
                ['min' => 20001, 'max' => 25000, 'charge' => 67],
                ['min' => 25001, 'max' => 30000, 'charge' => 72],
                ['min' => 30001, 'max' => 35000, 'charge' => 83],
                ['min' => 35001, 'max' => 40000, 'charge' => 99],
                ['min' => 40001, 'max' => 45000, 'charge' => 103],
                ['min' => 45001, 'max' => 250000, 'charge' => 108],
            ],
        ],
        'providers' => [
            'mpesa' => [
                'pay_bill_switch_amount' => env('SIDOOH_PAYBILL_SWITCH_AMOUNT'),
                'pay_bill'               => [
                    'key'       => env('SIDOOH_PAYBILL_KEY'),
                    'secret'    => env('SIDOOH_PAYBILL_SECRET'),
                    'passkey'   => env('SIDOOH_PAYBILL_PASS_KEY'),
                    'shortcode' => env('SIDOOH_PAYBILL_SHORTCODE'),
                ],
                'stk'   => env('SIDOOH_STK_PROVIDER', 'MPESA'),
                'b2b'   => env('SIDOOH_B2B_PROVIDER', 'MPESA'),
                'b2b_balance_threshold' => 100000,
            ],
            'buni' => [
                'till' => env('BUNI_TILL_NUMBER')
            ],
        ],
        'merchants' => [
            'blacklist' => [888888, 888880]
        ],
        'admin_contacts'     => env('ADMIN_CONTACTS', '254110039317,254714611696'),
    ],
];
