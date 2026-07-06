<?php

return [
    'store_id' => env('SSLCOMMERZ_STORE_ID', ''),
    'store_password' => env('SSLCOMMERZ_STORE_PASSWORD', ''),
    'sandbox' => env('SSLCOMMERZ_SANDBOX', true),
    'urls' => [
        'sandbox' => [
            'api' => 'https://sandbox.sslcommerz.com/gwprocess/v3/api.php',
            'validator' => 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php',
        ],
        'live' => [
            'api' => 'https://secure.sslcommerz.com/gwprocess/v3/api.php',
            'validator' => 'https://secure.sslcommerz.com/validator/api/validationserverAPI.php',
        ],
    ],
    'currencies' => ['BDT', 'USD', 'EUR'],
    'default_currency' => 'BDT',
];
