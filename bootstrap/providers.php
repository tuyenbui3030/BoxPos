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
    Packages\Product\ProductServiceProvider::class,
    Packages\Inventory\InventoryServiceProvider::class,
    Packages\Employee\EmployeeServiceProvider::class,
    Packages\Finance\FinanceServiceProvider::class,
    Packages\Order\OrderServiceProvider::class,
    Packages\Report\ReportServiceProvider::class,
    Packages\Archive\ArchiveServiceProvider::class,
];
