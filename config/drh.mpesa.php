<?php

return [
    /*
     |------------------------------------------------------
     | Set sandbox mode
     | ------------------------------------------------------
     | Specify whether this is a test app or production app
     | Sandbox base url: https://sandbox.safaricom.co.ke
     | Production base url: https://api.safaricom.co.ke
     |
     */
    'sandbox' => env('MPESA_SANDBOX', true),
    /*
     |------------------------------------------------------
     | Set multi tenancy mode
     | ------------------------------------------------------
     | Specify whether to use library with multi tenancy support (i.e. support multiple paybills)
     |
     */
    'multi_tenancy' => env('MPESA_MULTI_TENANCY', false),
    /*
   |--------------------------------------------------------------------------
   | Cache credentials
   |--------------------------------------------------------------------------
   |
   | If you decide to cache credentials, they will be kept in your app cache
   | configuration for some time. Reducing the need for many requests for
   | generating credentials
   |
   */
    'cache_credentials' => true,

    /*
  |--------------------------------------------------------------------------
  | Retry Requests on Failure
  |--------------------------------------------------------------------------
  |
  | If you decide to retry requests, they will be retried only
  | if there was a connection failure/exception
  | Retry wait time is the time between failure and request retry in seconds
  | Set retries to 0(Zero) to skip retrying
  |
  */
    'retries'         => 2,
    'retry_wait_time' => 1,

    /*
   |--------------------------------------------------------------------------
   | C2B array
   |--------------------------------------------------------------------------
   |
   | If you are accepting payments enter application details and shortcode info
   |
   */
    'c2b' => [
        /*
         * Consumer Key from developer portal
         */
        'consumer_key' => env('MPESA_KEY', '1mLKGyGUdx4BfosXGDQtVXWFRD9By8xu'),
        /*
         * Consumer secret from developer portal
         */
        'consumer_secret' => env('MPESA_SECRET', 'kEuTSaAlzZdxI3dc'),
        /*
         * HTTP callback method [POST,GET]
         */
        'callback_method' => 'POST',
        /*
         * Your receiving paybill or till number
         */
        'short_code' => env('MPESA_C2B_SHORTCODE', '174379'),
        /*
         * Transaction type based on shortcode business type
         */
        'transaction_type' => env('MPESA_C2B_TRANSACTION_TYPE', 'CustomerPayBillOnline'),
        /*
         * Optional Till number if different from shortcode
         */
        'party_b' => env('MPESA_C2B_PARTY_B'),
        /*
         * Passkey , requested from mpesa
         */
        'passkey' => env('MPESA_C2B_PASS_KEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'),
        /*
         * --------------------------------------------------------------------------------------
         * Callbacks:
         * ---------------------------------------------------------------------------------------
         * Please update your app url in .env file
         * Note: This package has already routes for handling this callback.
         * You should leave this values as they are unless you know what you are doing
         */
        /*
         * Stk callback URL
         */
        'stk_callback' => env('APP_URL').'/payments/callbacks/stk-callback',
        /*
         * Data is sent to this URL for successful payment
         */
        'confirmation_url' => env('APP_URL').'/payments/callbacks/c2b-confirmation',
        /*
         * Mpesa validation URL.
         * NOTE: You need to email MPESA to enable validation
         */
        'validation_url' => env('APP_URL').'/payments/callbacks/c2b-validation',
    ],
    /*
      |--------------------------------------------------------------------------
      | B2C array
      |--------------------------------------------------------------------------
      |
      | If you are sending payments to customers or b2b
      |
      */
    'b2c' => [
        /*
         * Sending app consumer key
         */
        'consumer_key' => env('MPESA_B2C_KEY'),
        /*
         * Sending app consumer secret
         */
        'consumer_secret' => env('MPESA_B2C_SECRET'),
        /*
         * Shortcode sending funds
         */
        'short_code' => env('MPESA_B2C_SHORTCODE', '603021'),
        /*
        * This is the user initiating the transaction, usually from the Mpesa organization portal
        * Make sure this was the user who was used to 'GO LIVE'
        * https://org.ke.m-pesa.com/
        */
        'initiator' => env('MPESA_B2C_INITIATOR', 'apiop37'),
        /*
         * The user security credential.
         * Go to https://developer.safaricom.co.ke/test_credentials and paste your initiator password to generate
         * security credential
         */
        'security_credential' => env('MPESA_B2C_SECURITY_CREDENTIAL', 'ZW20xkR58Tm4E1CZxliomiGC9wKPnM+RE/+bbxPgFSbhU10PKRYFjjO2W0HVRjdpZQcw9VInadmPVrsN+SramgZBg6Jix6NslJa+npItFRZyiI5eodSOKR2h7Fm/HpjOJAYvPBBBbBwvom+fJv06l4wIpDOkiiTY5+qx8J+FSZ/c4iVRSaDN5VHVXvUXJqsIRvoc0sLSU+EwJYgE4lx/J8gyhokWVBUCvxjOW/mOymi0rbESByKU2IXA3D2+ds5n+XwcrxB+n0Ub7WDw+ia0N1ixn2HqHpfaizp20FywVlw3AxHpueyRWrbzeo8jzCmG3ZBU0xdIMCiTeVBPGNUz1A=='),
        /*
         * Notification URL for timeout
         */
        'timeout_url' => env('APP_URL').'/payments/callbacks/timeout/',
        /**
         * Result URL
         */
        'result_url' => env('APP_URL').'/payments/callbacks/result/',
    ],

    /*
     |------------------------------------------------------
     | Set sandbox amount
     | ------------------------------------------------------
     | Specify whether to use actual amount on sandbox
     | 0 - actual amount, any other value will use that value
     |
     */
    'sandbox_test_amount' => env('MPESA_SANDBOX_AMOUNT', 1),

    'logging' => [
        'enabled'  => env('MPESA_ENABLE_LOGGING', false),
        'channels' => [
            'syslog',
            'single' => [
                'driver' => 'single',
                'path'   => storage_path('logs/mpesa.log'),
                'level'  => env('LOG_LEVEL', 'debug'),
            ],
        ],
    ],
];
