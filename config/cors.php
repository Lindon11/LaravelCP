<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | SECURITY NOTE: In production, CORS_ALLOWED_ORIGINS should be set to
    | specific domains (e.g., "https://yourdomain.com,https://admin.yourdomain.com")
    | Never use '*' in production with credentials enabled.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'livewire/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5175,http://localhost:8001')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'X-XSRF-TOKEN', 'Accept'],

    'exposed_headers' => ['X-RateLimit-Limit', 'X-RateLimit-Remaining'],

    'max_age' => 86400, // 24 hours - cache preflight requests

    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', true),

];
