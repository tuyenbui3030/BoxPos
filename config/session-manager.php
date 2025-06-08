<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Simple Session Extension Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the simplified middleware-based session extension
    | approach. This just automatically extends sessions whenever users
    | make HTTP requests.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime Settings  
    |--------------------------------------------------------------------------
    |
    | Define how long sessions should remain active when user has activity.
    | The middleware will automatically extend this session on every request.
    | 
    | Unit: minutes
    | Examples: 60 = 1 hour, 120 = 2 hours, 1440 = 1 day, 10080 = 7 days
    |
    */
    'session_lifetime' => env('SESSION_LIFETIME_MINUTES', 120), // 2 hours default
    
    /*
    |--------------------------------------------------------------------------
    | Database Update Throttle
    |--------------------------------------------------------------------------
    |
    | Minimum time between database last_login_at updates to prevent 
    | excessive database writes on every request.
    | 
    | Unit: seconds
    | Default: 600 (10 minutes)
    |
    */
    'db_update_throttle' => env('SESSION_DB_THROTTLE_SECONDS', 600), // 10 minutes

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
        'log_extensions' => env('SESSION_DEBUG_LOG_EXTENSIONS', false),
        'log_activity' => env('SESSION_DEBUG_LOG_ACTIVITY', false),
    ],
];
