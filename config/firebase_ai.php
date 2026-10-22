<?php

return [
    'web' => [
        'api_key' => env('FIREBASE_AI_WEB_API_KEY'),
        'auth_domain' => env('FIREBASE_AI_WEB_AUTH_DOMAIN'),
        'project_id' => env('FIREBASE_AI_WEB_PROJECT_ID', 'mecxihubai'),
        'storage_bucket' => env('FIREBASE_AI_WEB_STORAGE_BUCKET'),
        'messaging_sender_id' => env('FIREBASE_AI_WEB_MESSAGING_SENDER_ID'),
        'app_id' => env('FIREBASE_AI_WEB_APP_ID'),
    ],
    'app_check' => [
        'recaptcha_site_key' => env('FIREBASE_AI_APPCHECK_SITE_KEY'),
        'debug' => env('FIREBASE_AI_APPCHECK_DEBUG', true),
    ],
];
