<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Store Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the Store package.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Store Settings
    |--------------------------------------------------------------------------
    |
    | These are the default settings that will be applied to new stores.
    |
    */
    'defaults' => [
        'status' => 'active',
        'timezone' => 'UTC',
        'currency' => 'USD',
        'language' => 'en',
        'settings' => [
            'allow_guest_checkout' => false,
            'require_email_verification' => true,
            'auto_approve_customers' => true,
            'default_customer_group' => 'general',
            'inventory_tracking' => true,
            'low_stock_threshold' => 10,
            'tax_calculation' => 'inclusive',
            'default_tax_rate' => 0.0,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Store Roles and Permissions
    |--------------------------------------------------------------------------
    |
    | Define the available roles and their default permissions.
    |
    */
    'roles' => [
        'admin' => [
            'name' => 'Administrator',
            'description' => 'Full access to all store features',
            'permissions' => [
                'view_dashboard',
                'manage_customers',
                'manage_products',
                'manage_orders',
                'manage_inventory',
                'manage_reports',
                'manage_settings',
                'manage_users',
            ],
        ],
        'manager' => [
            'name' => 'Manager',
            'description' => 'Manage store operations and view reports',
            'permissions' => [
                'view_dashboard',
                'manage_customers',
                'manage_products',
                'manage_orders',
                'manage_inventory',
                'manage_reports',
            ],
        ],
        'staff' => [
            'name' => 'Staff',
            'description' => 'Handle customer orders and basic operations',
            'permissions' => [
                'view_dashboard',
                'manage_customers',
                'manage_orders',
            ],
        ],
        'viewer' => [
            'name' => 'Viewer',
            'description' => 'Read-only access to dashboard',
            'permissions' => [
                'view_dashboard',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Permissions
    |--------------------------------------------------------------------------
    |
    | All available permissions in the system.
    |
    */
    'permissions' => [
        'view_dashboard' => 'View Dashboard',
        'manage_customers' => 'Manage Customers',
        'manage_products' => 'Manage Products',
        'manage_orders' => 'Manage Orders',
        'manage_inventory' => 'Manage Inventory',
        'manage_reports' => 'Manage Reports',
        'manage_settings' => 'Manage Settings',
        'manage_users' => 'Manage Users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Store Validation Rules
    |--------------------------------------------------------------------------
    |
    | Validation rules for store creation and updates.
    |
    */
    'validation' => [
        'name' => 'required|string|max:255',
        'slug' => 'nullable|string|max:255|unique:stores,slug',
        'domain' => 'nullable|string|max:255|unique:stores,domain',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
        'timezone' => 'required|string|max:50',
        'currency' => 'required|string|size:3',
        'language' => 'required|string|size:2',
        'status' => 'required|in:active,inactive,suspended',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    |
    | List of supported currencies for stores.
    |
    */
    'currencies' => [
        'USD' => 'US Dollar',
        'EUR' => 'Euro',
        'GBP' => 'British Pound',
        'JPY' => 'Japanese Yen',
        'VND' => 'Vietnamese Dong',
        'CNY' => 'Chinese Yuan',
        'KRW' => 'South Korean Won',
        'THB' => 'Thai Baht',
        'SGD' => 'Singapore Dollar',
        'MYR' => 'Malaysian Ringgit',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    |
    | List of supported languages for stores.
    |
    */
    'languages' => [
        'en' => 'English',
        'vi' => 'Vietnamese',
        'zh' => 'Chinese',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'th' => 'Thai',
        'ms' => 'Malay',
        'id' => 'Indonesian',
    ],

    /*
    |--------------------------------------------------------------------------
    | Store Logo Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for store logo uploads.
    |
    */
    'logo' => [
        'disk' => 'public',
        'path' => 'store-logos',
        'max_size' => 2048, // KB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'svg'],
        'dimensions' => [
            'max_width' => 500,
            'max_height' => 500,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for store data.
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'store:',
        'tags' => ['stores'],
    ],
];
