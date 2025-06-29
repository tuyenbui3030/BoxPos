<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the Tenant package.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Tenant Column Name
    |--------------------------------------------------------------------------
    |
    | The column name used to identify the tenant (store) in database tables.
    |
    */
    'tenant_column' => 'store_id',

    /*
    |--------------------------------------------------------------------------
    | Auto Apply Tenant Scope
    |--------------------------------------------------------------------------
    |
    | Whether to automatically apply tenant scope to models that use the
    | HasTenantScope trait.
    |
    */
    'auto_apply_scope' => true,

    /*
    |--------------------------------------------------------------------------
    | Tenant Scoped Models
    |--------------------------------------------------------------------------
    |
    | List of models that should be automatically tenant-scoped.
    |
    */
    'scoped_models' => [
        \Packages\Customer\Models\Customer::class,
        // Add other models here as they are created
        // \Packages\Product\Models\Product::class,
        // \Packages\Order\Models\Order::class,
        // \Packages\Inventory\Models\InventoryItem::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes Excluded from Tenant Context
    |--------------------------------------------------------------------------
    |
    | Routes that don't require tenant context to be set.
    |
    */
    'excluded_routes' => [
        'store.selection',
        'store.switch',
        'logout',
        'profile.*',
        'api.*',
        'login',
        'register',
        'password.*',
        'verification.*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Store Selection Redirect
    |--------------------------------------------------------------------------
    |
    | The route to redirect to when user doesn't have a current store set.
    |
    */
    'store_selection_route' => 'store.selection',

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for tenant data.
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'tenant:',
        'tags' => ['tenant-data'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Context Initialization
    |--------------------------------------------------------------------------
    |
    | Settings for tenant context initialization.
    |
    */
    'context' => [
        'auto_set_locale' => true,
        'auto_set_timezone' => true,
        'auto_set_currency' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for tenant-related logging.
    |
    */
    'logging' => [
        'log_store_switches' => true,
        'log_permission_checks' => false,
        'log_context_initialization' => true,
    ],
];
