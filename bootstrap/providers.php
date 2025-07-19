<?php

return [
    App\Providers\AppServiceProvider::class,
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
];
