<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Package Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the User package.
    | You can customize various aspects of user management here.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default User Role
    |--------------------------------------------------------------------------
    |
    | The default role assigned to new users when created.
    |
    */
    'default_role' => 'user',

    /*
    |--------------------------------------------------------------------------
    | User Roles
    |--------------------------------------------------------------------------
    |
    | Available user roles that can be assigned.
    |
    */
    'roles' => [
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'employee' => 'Employee',
        'user' => 'User',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Status
    |--------------------------------------------------------------------------
    |
    | Default status for new users.
    |
    */
    'default_status' => true, // Active by default

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Default number of users per page for listings.
    |
    */
    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for user authentication.
    |
    */
    'authentication' => [
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes in seconds
        'remember_duration' => 525600, // 1 year in minutes
        'session_lifetime' => 120, // 2 hours in minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for password requirements and policies.
    |
    */
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => false,
        'history_count' => 5, // Number of previous passwords to remember
        'expiry_days' => null, // Password expiry in days (null = never)
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for user data exports.
    |
    */
    'export' => [
        'formats' => ['csv', 'json', 'xlsx'],
        'default_format' => 'csv',
        'storage_path' => 'exports/users',
        'max_records' => 10000,
        'include_sensitive_by_default' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Custom validation rules for user fields.
    |
    */
    'validation' => [
        'name_max_length' => 255,
        'email_unique' => true,
        'username_unique' => true,
        'username_min_length' => 3,
        'username_max_length' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific user events.
    |
    */
    'events' => [
        'send_welcome_email' => true,
        'log_login_attempts' => true,
        'notify_password_change' => true,
        'track_user_activity' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for user search functionality.
    |
    */
    'search' => [
        'fields' => ['name', 'email', 'username'],
        'fuzzy_search' => true,
        'min_search_length' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Verification
    |--------------------------------------------------------------------------
    |
    | Configuration for email verification.
    |
    */
    'email_verification' => [
        'required' => false,
        'expiry_hours' => 24,
        'resend_cooldown_minutes' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication
    |--------------------------------------------------------------------------
    |
    | Configuration for 2FA settings.
    |
    */
    'two_factor' => [
        'enabled' => false,
        'required_for_roles' => ['admin', 'manager'],
        'backup_codes_count' => 8,
        'qr_code_size' => 200,
    ],
];
