<?php

return [
    /*
    |------------------------------------------------------
    | Set sandbox mode
    | ------------------------------------------------------
    | Specify whether this is a test app or production app
    |
    | Sandbox base url: https://uat.buni.kcbgroup.com
    | Production base url: https://api.buni.kcbgroup.com
    */
    'sandbox' => env('BUNI_SANDBOX', false),

    /*
    |--------------------------------------------------------------------------
    | Cache credentials/keys
    |--------------------------------------------------------------------------
    |
    | If you decide to cache credentials, they will be kept in your app cache
    | configuration for some time. Reducing the need for many requests for
    | generating credentials/encryption keys
    |
    */
    'cache_credentials' => true,

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Callback - Will be used to send you payment notifications.
    |
    */
    'urls' => [
        'base' => env('BUNI_URL'),
        /*
         * --------------------------------------------------------------------------------------
         * Callbacks:
         * ---------------------------------------------------------------------------------------
         * Please update your app url in .env file
         * Note: This package has already routes for handling this callback.
         * You should leave this values as they are unless you know what you are doing.
         */
        'stk_callback' => env('APP_URL') . '/buni/callbacks/stk',
    ],

    /*
    |--------------------------------------------------------------------------
    | Client Key
    |--------------------------------------------------------------------------
    |
    | Provided after account creation.
    |
    */
    'key' => env('BUNI_KEY'),


    /*
    |--------------------------------------------------------------------------
    | Secret
    |--------------------------------------------------------------------------
    |
    | Provided after account creation.
    |
    */
    'secret' => env('BUNI_SECRET'),


    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Whether to log in the library
    |
    */
    'logging' => [
        'enabled' => env('BUNI_ENABLE_LOGGING', false),
        'channels' => [
            'gcp'
        ],
    ],
];
