<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Marketplace Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the BoxPos Marketplace package
    |
    */

    'trial_duration_days' => env('MARKETPLACE_TRIAL_DAYS', 14),
    
    'default_billing_cycle' => env('MARKETPLACE_BILLING_CYCLE', 'monthly'),
    
    'payment_methods' => [
        'demo' => 'Demo Payment',
        'trial' => 'Free Trial',
        'vnpay' => 'VNPay',
        'momo' => 'MoMo',
        'bank_transfer' => 'Bank Transfer',
    ],
    
    'subscription_statuses' => [
        'active' => 'Active',
        'suspended' => 'Suspended',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
    ],
    
    'plan_types' => [
        'trial' => 'Free Trial',
        'basic' => 'Basic Plan',
        'professional' => 'Professional Plan',
        'enterprise' => 'Enterprise Plan',
    ],
    
    'app_categories' => [
        'healthcare' => 'Healthcare',
        'construction' => 'Construction & Building Materials',
        'food_beverage' => 'Food & Beverage',
        'beauty_wellness' => 'Beauty & Wellness',
        'retail' => 'General Retail',
        'services' => 'Professional Services',
    ],
    
    'features' => [
        // Core features
        'inventory_management' => 'Inventory Management',
        'sales_management' => 'Sales Management',
        'customer_management' => 'Customer Management',
        'basic_reports' => 'Basic Reports',
        
        // Advanced features
        'advanced_reports' => 'Advanced Reports & Analytics',
        'multi_store' => 'Multi-Store Management',
        'api_access' => 'API Access',
        'priority_support' => 'Priority Support',
        'custom_integrations' => 'Custom Integrations',
        'white_label' => 'White Label Branding',
        
        // App-specific features
        'appointment_scheduling' => 'Appointment Scheduling',
        'patient_records' => 'Patient Records',
        'prescription_management' => 'Prescription Management',
        'table_management' => 'Table Management',
        'kitchen_display' => 'Kitchen Display System',
        'supplier_management' => 'Supplier Management',
        'warehouse_management' => 'Warehouse Management',
    ],
    
    'pricing_tiers' => [
        'trial' => [
            'duration_days' => 14,
            'max_stores' => 1,
            'max_users' => 2,
            'storage_gb' => 1,
        ],
        'basic' => [
            'max_stores' => 1,
            'max_users' => 5,
            'storage_gb' => 10,
        ],
        'professional' => [
            'max_stores' => 3,
            'max_users' => 15,
            'storage_gb' => 50,
        ],
        'enterprise' => [
            'max_stores' => -1, // Unlimited
            'max_users' => -1,  // Unlimited
            'storage_gb' => 500,
        ],
    ],
    
    'notifications' => [
        'trial_expiry_warning_days' => 3,
        'subscription_expiry_warning_days' => 7,
        'payment_failure_retry_days' => 3,
    ],
];