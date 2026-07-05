<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Active AI Agent Provider
    |--------------------------------------------------------------------------
    |
    | Options: rasa, botpress, botframework
    |
    */

    'provider' => env('AI_AGENT_PROVIDER', 'rasa'),

    /*
    |--------------------------------------------------------------------------
    | Rasa Configuration
    |--------------------------------------------------------------------------
    */

    'rasa' => [
        'url' => env('RASA_URL', 'http://localhost:5005'),
        'action_url' => env('RASA_ACTION_URL', 'http://localhost:5055'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Botpress Configuration
    |--------------------------------------------------------------------------
    */

    'botpress' => [
        'url' => env('BOTPRESS_URL', 'http://localhost:3100'),
        'bot_id' => env('BOTPRESS_BOT_ID', 'ecommerce-bot'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Microsoft Bot Framework Configuration
    |--------------------------------------------------------------------------
    */

    'botframework' => [
        'app_id' => env('BOTFRAMEWORK_APP_ID', ''),
        'app_password' => env('BOTFRAMEWORK_APP_PASSWORD', ''),
        'channel_id' => env('BOTFRAMEWORK_CHANNEL_ID', 'webchat'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Handoff Configuration
    |--------------------------------------------------------------------------
    */

    'handoff' => [
        'enabled' => env('AI_HANDOFF_ENABLED', true),
        'trigger_keywords' => ['human', 'agent', 'person', 'support', 'help'],
    ],

];
