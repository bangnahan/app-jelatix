<?php

return [
    'api_key' => env('TRIPAY_API_KEY', ''),
    'private_key' => env('TRIPAY_PRIVATE_KEY', ''),
    'merchant_code' => env('TRIPAY_MERCHANT_CODE', ''),
    'mode' => env('TRIPAY_MODE', 'sandbox'), // sandbox | production

    'endpoints' => [
        'sandbox' => 'https://tripay.co.id/api-sandbox',
        'production' => 'https://tripay.co.id/api',
    ],

    'expiry_minutes' => env('TRIPAY_EXPIRY_MINUTES', 30), // Standar hold kuota war tiket
];
