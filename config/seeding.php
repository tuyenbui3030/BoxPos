<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Development Environment Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for seeding in development environment.
    | This will create rich data for demonstration and testing purposes.
    |
    */
    'development' => [
        // User and Store Configuration
        'stores_count' => 3,
        'users_per_store' => 5,
        'admin_users_count' => 2,

        // Material Management
        'material_categories_count' => 15,
        'material_units_count' => 20,
        'materials_per_category' => 10,
        'suppliers_per_store' => 8,
        'materials_per_supplier' => 15,

        // Inventory Management
        'inventory_items_per_store' => 100,
        'inventory_movements_per_item' => 10,
        'stock_takes_per_store' => 5,
        'disposals_per_store' => 3,

        // Sales Management
        'customers_per_store' => 50,
        'product_categories_count' => 10,
        'products_per_category' => 8,
        'orders_per_customer' => 5,
        'order_items_per_order' => 3,

        // Employee Management
        'departments_count' => 6,
        'positions_per_department' => 4,
        'employees_per_store' => 15,
        'schedules_per_employee' => 30,
        'timesheets_per_employee' => 60,

        // Financial Management
        'cash_categories_count' => 8,
        'cash_accounts_per_store' => 5,
        'cash_transactions_per_account' => 50,
        'payment_methods_count' => 6,

        // Marketing & Loyalty
        'loyalty_programs_per_store' => 2,
        'loyalty_tiers_per_program' => 4,
        'promotions_per_store' => 10,
        'loyalty_transactions_per_member' => 15,

        // Reports & Notifications
        'report_templates_count' => 20,
        'report_dashboards_count' => 5,
        'notification_templates_count' => 15,
        'notifications_per_user' => 10,

        // Performance Settings
        'batch_size' => 100,
        'chunk_size' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Testing Environment Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for seeding in testing environment.
    | This will create minimal data needed for tests to run.
    |
    */
    'testing' => [
        // User and Store Configuration
        'stores_count' => 2,
        'users_per_store' => 2,
        'admin_users_count' => 1,

        // Material Management
        'material_categories_count' => 5,
        'material_units_count' => 8,
        'materials_per_category' => 3,
        'suppliers_per_store' => 3,
        'materials_per_supplier' => 5,

        // Inventory Management
        'inventory_items_per_store' => 10,
        'inventory_movements_per_item' => 3,
        'stock_takes_per_store' => 1,
        'disposals_per_store' => 1,

        // Sales Management
        'customers_per_store' => 5,
        'product_categories_count' => 3,
        'products_per_category' => 2,
        'orders_per_customer' => 2,
        'order_items_per_order' => 2,

        // Employee Management
        'departments_count' => 3,
        'positions_per_department' => 2,
        'employees_per_store' => 5,
        'schedules_per_employee' => 10,
        'timesheets_per_employee' => 20,

        // Financial Management
        'cash_categories_count' => 4,
        'cash_accounts_per_store' => 2,
        'cash_transactions_per_account' => 10,
        'payment_methods_count' => 3,

        // Marketing & Loyalty
        'loyalty_programs_per_store' => 1,
        'loyalty_tiers_per_program' => 3,
        'promotions_per_store' => 3,
        'loyalty_transactions_per_member' => 5,

        // Reports & Notifications
        'report_templates_count' => 5,
        'report_dashboards_count' => 2,
        'notification_templates_count' => 5,
        'notifications_per_user' => 3,

        // Performance Settings
        'batch_size' => 50,
        'chunk_size' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Production Environment Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for seeding in production environment.
    | This should only include essential system data.
    |
    */
    'production' => [
        // Only essential system data in production
        'stores_count' => 1,
        'users_per_store' => 1,
        'admin_users_count' => 1,

        // Minimal reference data
        'material_categories_count' => 10,
        'material_units_count' => 15,
        'materials_per_category' => 0, // No sample materials in production
        'suppliers_per_store' => 0,
        'materials_per_supplier' => 0,

        // No sample business data in production
        'inventory_items_per_store' => 0,
        'customers_per_store' => 0,
        'employees_per_store' => 0,
        'cash_transactions_per_account' => 0,
        'promotions_per_store' => 0,

        // Essential system templates only
        'report_templates_count' => 10,
        'notification_templates_count' => 8,
        'payment_methods_count' => 5,

        // Performance Settings
        'batch_size' => 25,
        'chunk_size' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Seeder Dependencies
    |--------------------------------------------------------------------------
    |
    | Define the order in which seeders should be executed to maintain
    | referential integrity and proper data relationships.
    |
    */
    'execution_order' => [
        // Core system seeders (no store dependency)
        'core' => [
            'Database\Seeders\StoreSeeder',
            'Database\Seeders\UserSeeder',
        ],

        // Business seeders (store dependent) - Level 1
        'business_level_1' => [
            'Packages\MaterialCatalog\Database\Seeders\MaterialCatalogSeeder',
            'Packages\Customer\Database\Seeders\CustomerSeeder',
            'Packages\Employees\Database\Seeders\EmployeeSeeder',
            'Packages\Products\Database\Seeders\ProductSeeder',
        ],

        // Business seeders (store dependent) - Level 2
        'business_level_2' => [
            'Packages\MaterialSuppliers\Database\Seeders\MaterialSupplierSeeder',
            'Packages\MaterialInventory\Database\Seeders\MaterialInventorySeeder',
            'Packages\MaterialPricing\Database\Seeders\MaterialPricingSeeder',
            'Packages\CashManagement\Database\Seeders\CashManagementSeeder',
            'Packages\Loyalty\Database\Seeders\LoyaltySeeder',
        ],

        // Business seeders (store dependent) - Level 3
        'business_level_3' => [
            'Packages\SalesOrders\Database\Seeders\SalesOrderSeeder',
            'Packages\Payments\Database\Seeders\PaymentSeeder',
            'Packages\Promotions\Database\Seeders\PromotionSeeder',
        ],

        // System seeders (final)
        'system' => [
            'Packages\Reports\Database\Seeders\ReportSeeder',
            'Packages\Notifications\Database\Seeders\NotificationSeeder',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Templates
    |--------------------------------------------------------------------------
    |
    | Predefined data templates for consistent seeding across environments.
    |
    */
    'templates' => [
        'material_categories' => [
            'Xi măng', 'Sắt thép', 'Gạch', 'Cát', 'Đá', 'Ngói', 'Ống nước',
            'Dây điện', 'Sơn', 'Keo dán', 'Vữa', 'Tôn', 'Nhựa', 'Gỗ', 'Kính'
        ],

        'material_units' => [
            'kg', 'm3', 'cái', 'bao', 'tấn', 'm2', 'mét', 'lít', 'thùng',
            'cuộn', 'tấm', 'viên', 'bộ', 'chiếc', 'gói', 'hộp', 'chai', 'lon', 'túi', 'bịch'
        ],

        'departments' => [
            'Bán hàng', 'Kho', 'Kế toán', 'Quản lý', 'Bảo vệ', 'Vận chuyển'
        ],

        'cash_categories' => [
            'Thu tiền bán hàng', 'Chi mua hàng', 'Thu nợ khách hàng', 'Trả nợ nhà cung cấp',
            'Chi phí vận hành', 'Thu khác', 'Chi khác', 'Chuyển khoản nội bộ'
        ],

        'payment_methods' => [
            'Tiền mặt', 'Chuyển khoản', 'Thẻ tín dụng', 'Thẻ ghi nợ', 'Ví điện tử', 'Trả góp'
        ],

        'customer_types' => [
            'Khách lẻ', 'Khách sỉ', 'Đại lý', 'Nhà thầu', 'VIP'
        ],

        'product_categories' => [
            'Dịch vụ thi công', 'Dịch vụ vận chuyển', 'Dịch vụ tư vấn',
            'Dịch vụ bảo trì', 'Dịch vụ thiết kế', 'Dịch vụ khác'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Rules for validating seeded data integrity.
    |
    */
    'validation' => [
        'required_stores_minimum' => 1,
        'required_admin_users_minimum' => 1,
        'max_records_per_batch' => 1000,
        'required_material_categories_minimum' => 5,
        'required_material_units_minimum' => 10,
    ],
];