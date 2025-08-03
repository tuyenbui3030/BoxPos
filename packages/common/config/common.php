<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Store Context Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for store context and multi-tenancy
    |
    */
    'store_context' => [
        // Cache TTL for store access checks (in seconds)
        'cache_ttl' => env('STORE_CONTEXT_CACHE_TTL', 300),
        
        // Default store selection route
        'store_selection_route' => env('STORE_SELECTION_ROUTE', 'stores.select'),
        
        // Enable store context validation
        'validate_context' => env('STORE_CONTEXT_VALIDATE', true),
        
        // Store switching permissions
        'allow_store_switching' => env('ALLOW_STORE_SWITCHING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the logging system
    |
    */
    'logging' => [
        // Log retention period in days
        'retention_days' => env('LOG_RETENTION_DAYS', 90),
        
        // Enable database logging
        'database_logging' => env('DATABASE_LOGGING', true),
        
        // Log performance metrics
        'log_performance' => env('LOG_PERFORMANCE', true),
        
        // Performance threshold for slow operations (in seconds)
        'slow_operation_threshold' => env('SLOW_OPERATION_THRESHOLD', 1.0),
        
        // Enable security event logging
        'log_security_events' => env('LOG_SECURITY_EVENTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Repository Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for repository pattern implementation
    |
    */
    'repository' => [
        // Enable automatic store scoping
        'auto_store_scope' => env('REPOSITORY_AUTO_STORE_SCOPE', true),
        
        // Default pagination size
        'default_pagination' => env('REPOSITORY_DEFAULT_PAGINATION', 15),
        
        // Enable query logging
        'log_queries' => env('REPOSITORY_LOG_QUERIES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security-related configuration options
    |
    */
    'security' => [
        // Enable IP-based access restrictions
        'ip_restrictions' => env('SECURITY_IP_RESTRICTIONS', false),
        
        // Allowed IP addresses (comma-separated)
        'allowed_ips' => env('SECURITY_ALLOWED_IPS', ''),
        
        // Enable geolocation tracking
        'geolocation_tracking' => env('SECURITY_GEOLOCATION_TRACKING', false),
        
        // Session timeout in minutes
        'session_timeout' => env('SECURITY_SESSION_TIMEOUT', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Caching configuration for the common package
    |
    */
    'cache' => [
        // Cache prefix for common package
        'prefix' => env('COMMON_CACHE_PREFIX', 'common'),
        
        // Default cache driver
        'driver' => env('COMMON_CACHE_DRIVER', 'redis'),
        
        // Cache tags support
        'tags_enabled' => env('COMMON_CACHE_TAGS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions Configuration
    |--------------------------------------------------------------------------
    |
    | Default permissions and roles configuration
    |
    */
    'permissions' => [
        // Default permissions for new users
        'default_permissions' => [
            'read',
        ],
        
        // Admin permissions
        'admin_permissions' => [
            'admin',
            'store_admin',
            'manage_users',
            'manage_inventory',
            'process_sales',
            'view_reports',
            'manage_finances',
        ],
        
        // Manager permissions
        'manager_permissions' => [
            'manage_inventory',
            'process_sales',
            'view_reports',
            'manage_users',
        ],
        
        // Staff permissions
        'staff_permissions' => [
            'process_sales',
            'read',
        ],
    ],
];