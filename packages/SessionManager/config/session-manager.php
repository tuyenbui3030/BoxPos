<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Session Lifetime Settings  
    |--------------------------------------------------------------------------
    |
    | Session lifetime in minutes when user is active.
    | Middleware will automatically extend session on each request.
    | 
    | Examples: 60 = 1 hour, 120 = 2 hours, 1440 = 1 day, 10080 = 7 days
    |
    */
    'session_lifetime' => env('SESSION_LIFETIME_MINUTES', 120), // 2 hours default
    
    /*
    |--------------------------------------------------------------------------
    | Database Update Throttle
    |--------------------------------------------------------------------------
    |
    | Minimum time between database updates for last_login_at
    | to prevent excessive database calls. Unit: seconds
    |
    */
    'db_update_throttle' => env('SESSION_DB_UPDATE_THROTTLE', 600), // 10 minutes

    /*
    |--------------------------------------------------------------------------
    | Session Cleanup Settings
    |--------------------------------------------------------------------------
    |
    | Settings for cleaning up expired sessions
    |
    */
    'cleanup' => [
        'retention_days' => env('SESSION_RETENTION_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Settings (Optional)
    |--------------------------------------------------------------------------
    |
    | Enable logging of session extensions for debugging
    |
    */
    'debug' => [
        'log_extensions' => env('SESSION_LOG_EXTENSIONS', false),
    ],
];
