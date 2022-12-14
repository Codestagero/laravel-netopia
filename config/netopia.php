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
    'soap_signature' => env('NETOPIA_SOAP_SIGNATURE', env('NETOPIA_SIGNATURE')),

    /*
    |--------------------------------------------------------------------------
    | Account Password
    |--------------------------------------------------------------------------
    |
    | The Netopia account password, hashed using MD5.
    |
    */
    'account_password_hash' => env('NETOPIA_ACCOUNT_PASSWORD_HASH'),

    /*
    |--------------------------------------------------------------------------
    | Netopia user name
    |--------------------------------------------------------------------------
    |
    | The Netopia account password, hashed using MD5.
    |
    */
    'username' => env('NETOPIA_USER_NAME', env('APP_NAME')),

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
    ],

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    |
    | Routing configuration.
    |
    */
    'domain' => env('NETOPIA_ROUTE_DOMAIN'),
    'route_prefix' => env('NETOPIA_ROUTE_PREFIX', 'netopia'),
    'route_middleware' => ['web'],
];
