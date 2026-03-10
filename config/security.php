<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações de segurança para o sistema de roteiros
    |
    */

    'admin' => [
        'password' => env('ADMIN_PASSWORD', 'Admin123!@#Seguro'),
        'email' => env('ADMIN_EMAIL', 'admin@admin.com'),
    ],

    'file_upload' => [
        'max_size' => 10240, // 10MB em KB
        'allowed_mimes' => [
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
        ],
        'scan_for_malware' => env('SCAN_UPLOADS', false),
    ],

    'rate_limiting' => [
        'login_attempts' => 5,
        'password_reset' => 3,
        'file_upload' => 10,
        'file_export' => 20,
        'general_requests' => 100,
        'driver' => env('RATE_LIMIT_DRIVER', 'database'), // database ou redis
    ],

    'headers' => [
        'x_content_type_options' => 'nosniff',
        'x_frame_options' => 'DENY',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'geolocation=(), microphone=(), camera=()',
    ],

    'content_security_policy' => [
        'default_src' => "'self'",
        'script_src' => "'self' 'unsafe-inline' 'unsafe-eval' https://fonts.bunny.net",
        'style_src' => "'self' 'unsafe-inline' https://fonts.bunny.net",
        'font_src' => "'self' https://fonts.bunny.net",
        'img_src' => "'self' data:",
        'connect_src' => "'self'",
    ],
];
