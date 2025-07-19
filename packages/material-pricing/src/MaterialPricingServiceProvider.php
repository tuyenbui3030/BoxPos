<?php

namespace Packages\MaterialPricing;

use Illuminate\Support\ServiceProvider;

class MaterialPricingServiceProvider extends ServiceProvider
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
            \Packages\MaterialPricing\Services\PricingService::class,
            \Packages\MaterialPricing\Services\PricingService::class
        );
    }
}
