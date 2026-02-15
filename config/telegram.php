<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the system monitoring Telegram bot.
    |
    */

    // Bot Token (Updated)
    'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),

    // Comma-separated list of chat IDs
    'chat_ids' => array_filter(explode(',', env('TELEGRAM_CHAT_IDS', ''))),

    // Bot Access Code (Security)
    'access_code' => env('TELEGRAM_ACCESS_CODE', ''), // Default simplified for dev

    // Master switch for all Telegram notifications
    'enabled' => env('TELEGRAM_ENABLED', false),

    // Individual feature toggles
    'log_errors' => env('TELEGRAM_LOG_ERRORS', true),
    'log_activities' => env('TELEGRAM_LOG_ACTIVITIES', true),
    'daily_report' => env('TELEGRAM_DAILY_REPORT', true),
    'daily_report_time' => env('TELEGRAM_DAILY_REPORT_TIME', '08:00'),

    // Error types to ignore (won't send notifications for these)
    'ignored_exceptions' => [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Validation\ValidationException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
    ],

    // Rate limiting: max messages per minute to avoid spam
    'rate_limit' => [
        'max_messages' => 30,
        'per_minutes' => 1,
    ],
];
