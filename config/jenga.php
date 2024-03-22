<?php

return [

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
