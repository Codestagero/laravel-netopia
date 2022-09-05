<?php

use Codestage\Netopia\Enums\PaymentStatus;

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

    /*
    |--------------------------------------------------------------------------
    | Payable statuses
    |--------------------------------------------------------------------------
    |
    | Payment statuses for which further action can be taken.
    | If a user tries to execute a payment that has other type than these, a 403 Forbidden response will be returned
    |
    */
    'payable_statuses' => [
        PaymentStatus::NotStarted,
        PaymentStatus::Rejected
    ]
];
