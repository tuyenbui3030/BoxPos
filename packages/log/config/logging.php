<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the Log package. This extends Laravel's
    | default logging configuration with additional channels and options
    | for SQL query logging, performance monitoring, and log management.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | SQL Query Logging
    |--------------------------------------------------------------------------
    |
    | Enable or disable SQL query logging. When enabled, all database queries
    | will be logged to the sql.log file. Slow queries will be logged with
    | warning level if they exceed the threshold.
    |
    */

    'sql' => [
        'enabled' => env('LOG_SQL', false),
        'slow_queries' => env('LOG_SLOW_QUERIES', true),
        'threshold_ms' => env('SLOW_QUERY_THRESHOLD', 500),
        'detect_n_plus_one' => env('DETECT_N_PLUS_ONE', true),
        'max_queries_per_request' => env('MAX_QUERIES_PER_REQUEST', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Logging
    |--------------------------------------------------------------------------
    |
    | Configure HTTP request and response logging. This includes API requests,
    | web requests, and their corresponding responses with timing information.
    |
    */

    'requests' => [
        'enabled' => env('LOG_REQUESTS', true),
        'include_headers' => env('LOG_REQUEST_HEADERS', false),
        'include_body' => env('LOG_REQUEST_BODY', false),
        'exclude_paths' => [
            '/health',
            '/ping',
            '/metrics',
            '/_debugbar',
        ],
        'sensitive_fields' => [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'api_key',
            'secret',
            'credit_card',
            'card_number',
            'cvv',
            'ssn',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Monitor application performance including memory usage, execution time,
    | and resource consumption. Logs warnings when thresholds are exceeded.
    |
    */

    'performance' => [
        'enabled' => env('LOG_PERFORMANCE', true),
        'memory_threshold_mb' => env('MEMORY_THRESHOLD_MB', 100),
        'execution_threshold_ms' => env('EXECUTION_THRESHOLD_MS', 1000),
        'track_memory_peaks' => env('TRACK_MEMORY_PEAKS', true),
        'track_query_count' => env('TRACK_QUERY_COUNT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log File Management
    |--------------------------------------------------------------------------
    |
    | Configure log file rotation, compression, and retention policies.
    | Helps manage disk space and maintain log history.
    |
    */

    'file_management' => [
        'retention_days' => env('LOG_RETENTION_DAYS', 14),
        'compress' => env('LOG_COMPRESS', true),
        'max_file_size' => env('LOG_MAX_SIZE', '100M'),
        'rotation_schedule' => env('LOG_ROTATION_SCHEDULE', 'daily'), // daily, weekly, monthly
        'cleanup_enabled' => env('LOG_CLEANUP_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Remote File Storage
    |--------------------------------------------------------------------------
    |
    | Configure remote file storage for logs. Useful for centralized logging
    | in distributed environments or for backup purposes.
    |
    */

    'remote_file' => [
        'enabled' => env('LOG_TO_REMOTE_FILE', false),
        'path' => env('REMOTE_LOG_PATH', '/var/log/shared/laravel-logs'),
        'mount_point' => env('REMOTE_LOG_MOUNT_POINT', '/mnt/logs'),
        'sync_interval' => env('REMOTE_LOG_SYNC_INTERVAL', 300), // seconds
        'retry_attempts' => env('REMOTE_LOG_RETRY_ATTEMPTS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Slack Notifications
    |--------------------------------------------------------------------------
    |
    | Configure Slack notifications for critical errors and alerts.
    | Useful for immediate notification of production issues.
    |
    */

    'slack' => [
        'enabled' => env('LOG_TO_SLACK', false),
        'webhook_url' => env('SLACK_WEBHOOK_URL'),
        'channel' => env('SLACK_LOG_CHANNEL', '#alerts'),
        'username' => env('SLACK_LOG_USERNAME', 'BoxPos Logger'),
        'icon_emoji' => env('SLACK_LOG_ICON', ':warning:'),
        'min_level' => env('SLACK_MIN_LEVEL', 'error'),
        'include_context' => env('SLACK_INCLUDE_CONTEXT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Formatters
    |--------------------------------------------------------------------------
    |
    | Configure how logs are formatted. JSON format is recommended for
    | structured logging and future CloudWatch integration.
    |
    */

    'formatters' => [
        'default' => env('LOG_FORMATTER', 'line'), // line, json, structured
        'json_pretty_print' => env('LOG_JSON_PRETTY', false),
        'include_trace' => env('LOG_INCLUDE_TRACE', false),
        'max_trace_lines' => env('LOG_MAX_TRACE_LINES', 10),
        'include_request_id' => env('LOG_INCLUDE_REQUEST_ID', true),
        'include_user_id' => env('LOG_INCLUDE_USER_ID', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Log Channels
    |--------------------------------------------------------------------------
    |
    | Define custom log channels for different types of logs. This extends
    | Laravel's default channels with specialized channels for SQL, errors,
    | and performance logs.
    |
    */

    'channels' => [
        'sql' => [
            'driver' => 'daily',
            'path' => storage_path('logs/sql.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_RETENTION_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/error.log'),
            'level' => 'error',
            'days' => env('LOG_RETENTION_DAYS', 14),
        ],

        'performance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/performance.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_RETENTION_DAYS', 14),
        ],

        'remote_file' => [
            'driver' => 'custom',
            'via' => Packages\Log\Channels\RemoteFileChannel::class,
            'path' => env('REMOTE_LOG_PATH', '/var/log/shared/laravel-logs'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'slack_critical' => [
            'driver' => 'custom',
            'via' => Packages\Log\Channels\SlackChannel::class,
            'webhook' => env('SLACK_WEBHOOK_URL'),
            'level' => 'critical',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Activity Logging
    |--------------------------------------------------------------------------
    |
    | Configure user activity logging for audit trails and user behavior
    | analysis. This tracks user actions across the application.
    |
    */

    'user_activity' => [
        'enabled' => env('LOG_USER_ACTIVITY', true),
        'track_anonymous' => env('LOG_ANONYMOUS_ACTIVITY', false),
        'include_ip' => env('LOG_USER_IP', true),
        'include_user_agent' => env('LOG_USER_AGENT', true),
        'exclude_actions' => [
            'view',
            'index',
            'heartbeat',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Settings
    |--------------------------------------------------------------------------
    |
    | Settings specific to development environment for debugging and testing.
    |
    */

    'development' => [
        'log_all_queries' => env('LOG_ALL_QUERIES_DEV', false),
        'log_debug_info' => env('LOG_DEBUG_INFO', true),
        'log_memory_usage' => env('LOG_MEMORY_USAGE_DEV', true),
        'verbose_errors' => env('LOG_VERBOSE_ERRORS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | CloudWatch Preparation
    |--------------------------------------------------------------------------
    |
    | Settings to prepare logs for future CloudWatch integration.
    | These settings ensure logs are structured properly for CloudWatch.
    |
    */

    'cloudwatch_ready' => [
        'use_json_format' => env('USE_JSON_FORMAT', false),
        'include_metadata' => env('INCLUDE_LOG_METADATA', true),
        'batch_size' => env('LOG_BATCH_SIZE', 100),
        'flush_interval' => env('LOG_FLUSH_INTERVAL', 30), // seconds
        'log_group_prefix' => env('CLOUDWATCH_LOG_GROUP_PREFIX', 'boxpos'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sentry Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for Sentry error reporting and performance monitoring.
    | This integrates with the existing logging system to provide additional
    | error tracking and performance insights.
    |
    */

    'sentry' => [
        'enabled' => env('SENTRY_LARAVEL_DSN') !== null,
        'enable_local_tracking' => env('SENTRY_ENABLE_LOCAL_TRACKING', false),
        'report_slow_queries' => env('SENTRY_REPORT_SLOW_QUERIES', true),
        'report_n_plus_one' => env('SENTRY_REPORT_N_PLUS_ONE', true),
        'report_performance_issues' => env('SENTRY_REPORT_PERFORMANCE_ISSUES', true),
        'performance_thresholds' => [
            'execution_time_ms' => env('SENTRY_EXECUTION_THRESHOLD_MS', 1000),
            'memory_usage_mb' => env('SENTRY_MEMORY_THRESHOLD_MB', 100),
        ],
    ],
];
