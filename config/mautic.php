<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mautic API Configuration
    |--------------------------------------------------------------------------
    */

    'base_url' => env('MAUTIC_BASE_URL', 'http://localhost:8090'),

    'api' => [
        'username' => env('MAUTIC_API_USERNAME', ''),
        'password' => env('MAUTIC_API_PASSWORD', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracking Configuration
    |--------------------------------------------------------------------------
    */

    'tracking' => [
        'enabled' => env('MAUTIC_TRACKING_ENABLED', true),
        'pixel_id' => env('MAUTIC_PIXEL_ID', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */

    'webhook' => [
        'secret' => env('MAUTIC_WEBHOOK_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact Sync Configuration
    |--------------------------------------------------------------------------
    */

    'sync' => [
        'enabled' => env('MAUTIC_SYNC_ENABLED', true),
        'auto_create_contacts' => env('MAUTIC_AUTO_CREATE_CONTACTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Configuration
    |--------------------------------------------------------------------------
    */

    'email' => [
        'from_address' => env('MAUTIC_EMAIL_FROM', 'hello@example.com'),
        'from_name' => env('MAUTIC_EMAIL_FROM_NAME', 'OnClick2Buy'),
    ],

];
