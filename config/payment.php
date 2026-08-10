<?php

$cloverEnvironment = env('CLOVER_ENVIRONMENT', 'sandbox');

// Keep production and sandbox keys side-by-side. CLOVER_ENVIRONMENT picks which set is active.
// Legacy CLOVER_* vars still work as a fallback for the selected environment.
$cloverIsProduction = $cloverEnvironment === 'production';

$cloverMerchantId = $cloverIsProduction
    ? env('CLOVER_PRODUCTION_MERCHANT_ID', env('CLOVER_MERCHANT_ID', 'mock-clover-merchant-id'))
    : env('CLOVER_SANDBOX_MERCHANT_ID', env('CLOVER_MERCHANT_ID', 'mock-clover-merchant-id'));

$cloverAccessToken = $cloverIsProduction
    ? env('CLOVER_PRODUCTION_ACCESS_TOKEN', env('CLOVER_ACCESS_TOKEN', 'mock-clover-access-token'))
    : env('CLOVER_SANDBOX_ACCESS_TOKEN', env('CLOVER_ACCESS_TOKEN', 'mock-clover-access-token'));

$cloverEcommercePrivateKey = $cloverIsProduction
    ? env(
        'CLOVER_PRODUCTION_ECOMMERCE_PRIVATE_KEY',
        env('CLOVER_ECOMMERCE_PRIVATE_KEY', env('CLOVER_PRIVATE_KEY', $cloverAccessToken))
    )
    : env(
        'CLOVER_SANDBOX_ECOMMERCE_PRIVATE_KEY',
        env('CLOVER_ECOMMERCE_PRIVATE_KEY', env('CLOVER_PRIVATE_KEY', $cloverAccessToken))
    );

$cloverPublicKey = $cloverIsProduction
    ? env('CLOVER_PRODUCTION_PUBLIC_KEY', env('CLOVER_PUBLIC_KEY', env('CLOVER_PAKMS_KEY', '')))
    : env('CLOVER_SANDBOX_PUBLIC_KEY', env('CLOVER_PUBLIC_KEY', env('CLOVER_PAKMS_KEY', '')));

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Supported: "square", "clover"
    |
    | Switch Clover live ↔ test with CLOVER_ENVIRONMENT only.
    | Production and sandbox credentials can both stay in .env.
    |
    */
    'default' => env('PAYMENT_GATEWAY', 'clover'),

    /*
    |--------------------------------------------------------------------------
    | Gateway Configurations
    |--------------------------------------------------------------------------
    */
    'gateways' => [
        'square' => [
            'application_id' => env('SQUARE_APPLICATION_ID', ''),
            'access_token' => env('SQUARE_ACCESS_TOKEN', 'mock-square-access-token'),
            'location_id' => env('SQUARE_LOCATION_ID', 'mock-square-location-id'),
            'environment' => env('SQUARE_ENVIRONMENT', 'sandbox'), // sandbox or production
        ],
        'clover' => [
            // sandbox | production — picks which credential set below is active
            'environment' => $cloverEnvironment,
            'merchant_id' => $cloverMerchantId,
            // REST API private token (POS orders) for the active environment
            'access_token' => $cloverAccessToken,
            // Ecommerce private key (Bearer for /v1/charges)
            'ecommerce_private_key' => $cloverEcommercePrivateKey,
            // Ecommerce public key (Clover.js iframe / PAKMS)
            'public_key' => $cloverPublicKey,
        ],
    ],
];
