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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'firebase' => [
        'credentials' => env('FIREBASE_CREDENTIALS', base_path('google-services.json')),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'database_url' => env('FIREBASE_DATABASE_URL'),
    ],

    'whatsapp' => [
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'api_version' => env('WHATSAPP_API_VERSION', 'v20.0'),
        // The single approved WhatsApp template used for all notifications.
        'template_name' => env('WHATSAPP_TEMPLATE_NAME', 'payment_reminder'),
        'template_language' => env('WHATSAPP_TEMPLATE_LANGUAGE', 'en'),

        // Labels for the template's placeholders EXCLUDING the customer name
        // (that one is auto-filled per recipient from whatsapp_contacts.name).
        // e.g. payment_reminder is "Hi {{1}}, ... ₹{{2}} ... on {{3}}" ->
        // {{1}} = contact name (auto), {{2}} and {{3}} = these labels below.
        'template_param_labels' => ['Amount (₹)', 'Due Date'],

        // Zero-based position of the customer name placeholder within the
        // template's full parameter list.
        'name_param_index' => 0,
    ],

];
