<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging (FCM)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk mengirim push notification via Firebase.
    | Download service account JSON dari Firebase Console:
    | Project Settings > Service Accounts > Generate New Private Key
    |
    */
    'fcm' => [
        'credentials_path' => env('FCM_CREDENTIALS_PATH', storage_path('app/firebase/service-account.json')),
        'project_id' => env('FCM_PROJECT_ID', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Gemini (Google Generative Language API)
    |--------------------------------------------------------------------------
    */
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'temperature' => (float) env('GEMINI_TEMPERATURE', 0.4),
        'max_output_tokens' => (int) env('GEMINI_MAX_OUTPUT_TOKENS', 900),
        'score_delta_trigger' => (float) env('GEMINI_SCORE_DELTA_TRIGGER', 5),
    ],

];

