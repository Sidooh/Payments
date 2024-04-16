<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bill IPN
    |--------------------------------------------------------------------------
    |
    | Configs needed for the bill ipn
    |
    */
    'bill' => [
        'username' => 'EquityBillUser',
        'password' => 'B6n^cLD$6i$u',
    ],


    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Whether to log in the library
    |
    */
    'logging' => [
        'enabled' => env('JENGA_ENABLE_LOGGING', false),
        'channels' => [
            'gcp'
        ],
    ],
];
