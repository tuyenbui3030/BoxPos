<?php

namespace Packages\MaterialPurchasing;

use Illuminate\Support\ServiceProvider;

class MaterialPurchasingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register package services
        $this->registerServices();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load package migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }

    /**
     * Register package services.
     */
    protected function registerServices(): void
    {
        // Register repositories and services
        $this->app->bind(
            \Packages\MaterialPurchasing\Services\PurchaseOrderService::class,
            \Packages\MaterialPurchasing\Services\PurchaseOrderService::class
        );
    }
}
