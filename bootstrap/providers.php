<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\LocalizationServiceProvider::class,
    Livewire\LivewireServiceProvider::class,
    Packages\Customer\CustomerServiceProvider::class,
    Packages\User\UserServiceProvider::class,
    Packages\Store\StoreServiceProvider::class,
    Packages\Tenant\TenantServiceProvider::class,
    Packages\SessionManager\SessionManagerServiceProvider::class,
    Packages\Appearance\AppearanceServiceProvider::class,
    Packages\Log\LogServiceProvider::class,
    Packages\Localization\LocalizationServiceProvider::class,

    // Material Management Packages
    Packages\MaterialCatalog\MaterialCatalogServiceProvider::class,
    Packages\MaterialSuppliers\MaterialSuppliersServiceProvider::class,
    Packages\MaterialInventory\MaterialInventoryServiceProvider::class,
    Packages\MaterialPurchasing\MaterialPurchasingServiceProvider::class,
    Packages\MaterialPricing\MaterialPricingServiceProvider::class,

    // Product Management
    Packages\Products\ProductsServiceProvider::class,

    // Warehouse Management
    Packages\Warehouse\WarehouseServiceProvider::class,

    // Employee Management
    Packages\Employees\EmployeesServiceProvider::class,

    // Sales & Orders
    Packages\SalesOrders\SalesOrdersServiceProvider::class,

    // Financial Management
    Packages\CashManagement\CashManagementServiceProvider::class,
    Packages\Payments\PaymentsServiceProvider::class,

    // Marketing & Customer Engagement
    Packages\Promotions\PromotionsServiceProvider::class,
    Packages\Loyalty\LoyaltyServiceProvider::class,

    // Communication & Reporting
    Packages\Notifications\NotificationsServiceProvider::class,
    Packages\Reports\ReportsServiceProvider::class,

    // Marketplace & Subscriptions
    Packages\Marketplace\MarketplaceServiceProvider::class,
];
