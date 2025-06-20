<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Customer Package Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Customer package.
    | You can customize various aspects of customer management here.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Customer Status
    |--------------------------------------------------------------------------
    |
    | The default status assigned to new customers when created.
    |
    */
    'default_status' => 'active',

    /*
    |--------------------------------------------------------------------------
    | Customer Statuses
    |--------------------------------------------------------------------------
    |
    | Available customer statuses that can be assigned.
    |
    */
    'statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
        'pending' => 'Pending Verification',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Default number of customers per page for listings.
    |
    */
    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for customer data exports.
    |
    */
    'export' => [
        'formats' => ['csv', 'json', 'xlsx'],
        'default_format' => 'csv',
        'storage_path' => 'exports/customers',
        'max_records' => 10000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Custom validation rules for customer fields.
    |
    */
    'validation' => [
        'name_max_length' => 255,
        'email_unique' => true,
        'phone_format' => null, // Set to regex pattern if needed
        'address_max_length' => 500,
        'notes_max_length' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific customer events.
    |
    */
    'events' => [
        'send_welcome_email' => true,
        'log_updates' => true,
        'notify_on_status_change' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for customer search functionality.
    |
    */
    'search' => [
        'fields' => ['name', 'email', 'phone'],
        'fuzzy_search' => true,
        'min_search_length' => 2,
    ],
];
