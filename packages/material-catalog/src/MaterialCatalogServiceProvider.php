<?php

namespace Packages\MaterialCatalog;

use Illuminate\Support\ServiceProvider;

class MaterialCatalogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge package configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/material-catalog.php',
            'material-catalog'
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
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'material-catalog');

        // Load package translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'material-catalog');

        // Publish package assets
        $this->publishes([
            __DIR__ . '/../config/material-catalog.php' => config_path('material-catalog.php'),
        ], 'material-catalog-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/material-catalog'),
        ], 'material-catalog-views');

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/material-catalog'),
        ], 'material-catalog-lang');

        // Register commands if running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Add console commands here if needed
            ]);
        }
    }

    /**
     * Register package services.
     */
    protected function registerServices(): void
    {
        // Register repositories
        $this->app->bind(
            \Packages\MaterialCatalog\Repositories\MaterialUnitRepository::class,
            \Packages\MaterialCatalog\Repositories\MaterialUnitRepository::class
        );

        $this->app->bind(
            \Packages\MaterialCatalog\Repositories\MaterialCategoryRepository::class,
            \Packages\MaterialCatalog\Repositories\MaterialCategoryRepository::class
        );

        $this->app->bind(
            \Packages\MaterialCatalog\Repositories\BuildingMaterialRepository::class,
            \Packages\MaterialCatalog\Repositories\BuildingMaterialRepository::class
        );

        $this->app->bind(
            \Packages\MaterialCatalog\Repositories\MaterialSpecificationRepository::class,
            \Packages\MaterialCatalog\Repositories\MaterialSpecificationRepository::class
        );

        // Register services
        $this->app->bind(
            \Packages\MaterialCatalog\Services\MaterialUnitService::class,
            \Packages\MaterialCatalog\Services\MaterialUnitService::class
        );

        $this->app->bind(
            \Packages\MaterialCatalog\Services\MaterialCategoryService::class,
            \Packages\MaterialCatalog\Services\MaterialCategoryService::class
        );

        $this->app->bind(
            \Packages\MaterialCatalog\Services\BuildingMaterialService::class,
            \Packages\MaterialCatalog\Services\BuildingMaterialService::class
        );

        $this->app->bind(
            \Packages\MaterialCatalog\Services\MaterialSpecificationService::class,
            \Packages\MaterialCatalog\Services\MaterialSpecificationService::class
        );
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            \Packages\MaterialCatalog\Services\MaterialUnitService::class,
            \Packages\MaterialCatalog\Services\MaterialCategoryService::class,
            \Packages\MaterialCatalog\Services\BuildingMaterialService::class,
            \Packages\MaterialCatalog\Services\MaterialSpecificationService::class,
        ];
    }
}
