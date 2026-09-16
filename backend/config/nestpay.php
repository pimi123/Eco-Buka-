<?php

return [

    /*
    |--------------------------------------------------------------------------
    | NestPay Environment
    |--------------------------------------------------------------------------
    |
    | This value lets the application know which bank environment is being used
    | without changing source code. Expected values are local, development, or
    | production, but the value is informational for now.
    |
    */

    'environment' => env('NESTPAY_ENVIRONMENT', env('APP_ENV', 'local')),

    /*
    |--------------------------------------------------------------------------
    | NestPay Endpoints
    |--------------------------------------------------------------------------
    |
    | The 3D gateway URL is used for browser-based 3D Pay Hosting redirects.
    | The API URL is reserved for future server-to-server NestPay operations.
    |
    */

    'gateway_url' => env('NESTPAY_3D_GATEWAY_URL'),
    'api_url' => env('NESTPAY_API_URL'),

    /*
    |--------------------------------------------------------------------------
    | Merchant Configuration
    |--------------------------------------------------------------------------
    |
    | The store key is a private server-side secret. It must be configured only
    | in the backend environment and must never be exposed to frontend code.
    |
    */

    'client_id' => env('NESTPAY_CLIENT_ID'),
    'store_key' => env('NESTPAY_STORE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Payment Defaults
    |--------------------------------------------------------------------------
    |
    | These values match the selected NestPay 3D Pay Hosting integration mode.
    |
    */

    'store_type' => env('NESTPAY_STORE_TYPE', '3d_pay_hosting'),
    'transaction_type' => env('NESTPAY_TRANSACTION_TYPE', 'Auth'),
    'currency' => env('NESTPAY_CURRENCY', '978'),
    'language' => env('NESTPAY_LANGUAGE', 'en'),
    'hash_algorithm' => env('NESTPAY_HASH_ALGORITHM', 'ver3'),

    /*
    |--------------------------------------------------------------------------
    | Frontend Return URLs
    |--------------------------------------------------------------------------
    |
    | These URLs are sent to NestPay so the customer browser can return to the
    | storefront after the hosted 3D flow. They do not contain server secrets.
    |
    */

    'frontend_url' => env('FRONTEND_URL', 'http://127.0.0.1:5173'),
    // 'ok_url' => env('NESTPAY_OK_URL', rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/').'/api/payments/nestpay/success'),
    // 'fail_url' => env('NESTPAY_FAIL_URL', rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/').'/api/payments/nestpay/failure'),
    'ok_url' => env('NESTPAY_OK_URL'),
    'fail_url' => env('NESTPAY_FAIL_URL'),
    'shop_url' => env('NESTPAY_SHOP_URL'),

];
