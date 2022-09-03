<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | The environment that Netopia is running in.
    |
    | Valid values are: 'sandbox', 'production'
    |
    */
    'environment' => env('NETOPIA_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Signature
    |--------------------------------------------------------------------------
    |
    | The merchant signature provided by Netopia.
    |
    */
    'signature' => env('NETOPIA_SIGNATURE'),

    /*
    |--------------------------------------------------------------------------
    | Certificate paths
    |--------------------------------------------------------------------------
    |
    | The paths to the certificate files used for Netopia requests.
    |
    */
    'certificate_path' => [
        'public' => base_path('certificates/' . env('NETOPIA_PUBLIC_FILE', 'netopia.cer')),
        'secret' => base_path('certificates/' . env('NETOPIA_SECRET_FILE', 'netopia.key')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The currency used for Netopia.
    |
    */
    'currency' => env('NETOPIA_CURRENCY', 'EUR'),
];
