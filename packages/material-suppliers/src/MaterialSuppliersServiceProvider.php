<?php

namespace Packages\MaterialSuppliers;

use Illuminate\Support\ServiceProvider;

class MaterialSuppliersServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge package configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/material-suppliers.php',
            'material-suppliers'
        );

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

        // Load package routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load package views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'material-suppliers');

        // Load package translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'material-suppliers');

        // Publish package assets
        $this->publishes([
            __DIR__ . '/../config/material-suppliers.php' => config_path('material-suppliers.php'),
        ], 'material-suppliers-config');
    }

    /**
     * Register package services.
     */
    protected function registerServices(): void
    {
        // Register repositories
        $this->app->bind(
            \Packages\MaterialSuppliers\Repositories\MaterialSupplierRepository::class,
            \Packages\MaterialSuppliers\Repositories\MaterialSupplierRepository::class
        );

        $this->app->bind(
            \Packages\MaterialSuppliers\Repositories\SupplierContactRepository::class,
            \Packages\MaterialSuppliers\Repositories\SupplierContactRepository::class
        );

        // Register services
        $this->app->bind(
            \Packages\MaterialSuppliers\Services\MaterialSupplierService::class,
            \Packages\MaterialSuppliers\Services\MaterialSupplierService::class
        );

        $this->app->bind(
            \Packages\MaterialSuppliers\Services\SupplierContactService::class,
            \Packages\MaterialSuppliers\Services\SupplierContactService::class
        );
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            \Packages\MaterialSuppliers\Services\MaterialSupplierService::class,
            \Packages\MaterialSuppliers\Services\SupplierContactService::class,
        ];
    }
}
