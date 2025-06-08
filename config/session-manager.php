<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Session Keep Alive Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for session keep alive functionality
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Keep Alive Settings
    |--------------------------------------------------------------------------
    |
    | Settings for automatic session extension
    |
    */
    'keep_alive' => [
        'enabled' => env('SESSION_KEEP_ALIVE_ENABLED', true),
        'heartbeat_interval' => env('SESSION_HEARTBEAT_INTERVAL', 300), // 5 minutes in seconds
        'activity_timeout' => env('SESSION_ACTIVITY_TIMEOUT', 900), // 15 minutes in seconds
        'extend_threshold' => env('SESSION_EXTEND_THRESHOLD', 300), // 5 minutes in seconds
        'infinite_session' => env('SESSION_INFINITE_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Cleanup Settings
    |--------------------------------------------------------------------------
    |
    | Settings for cleaning up expired sessions
    |
    */
    'cleanup' => [
        'enabled' => env('SESSION_CLEANUP_ENABLED', true),
        'schedule' => env('SESSION_CLEANUP_SCHEDULE', 'daily'),
        'retention_days' => env('SESSION_RETENTION_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Settings
    |--------------------------------------------------------------------------
    |
    | Settings for debugging session management
    |
    */
    'debug' => [
        'enabled' => env('SESSION_DEBUG_ENABLED', false),
        'log_activity' => env('SESSION_LOG_ACTIVITY', false),
        'log_extensions' => env('SESSION_LOG_EXTENSIONS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security settings for session management
    |
    */
    'security' => [
        'regenerate_interval' => env('SESSION_REGENERATE_INTERVAL', 1800), // 30 minutes
        'ip_validation' => env('SESSION_IP_VALIDATION', false),
        'user_agent_validation' => env('SESSION_USER_AGENT_VALIDATION', false),
    ],
];
