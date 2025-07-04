<?php

namespace Packages\Product;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ProductServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register repositories
        $this->app->bind(
            \Packages\Product\Repositories\ProductRepositoryInterface::class,
            \Packages\Product\Repositories\ProductRepository::class
        );

        $this->app->bind(
            \Packages\Product\Repositories\ProductCategoryRepositoryInterface::class,
            \Packages\Product\Repositories\ProductCategoryRepository::class
        );

        // Register services
        $this->app->bind(
            \Packages\Product\Services\ProductServiceInterface::class,
            \Packages\Product\Services\ProductService::class
        );

        $this->app->bind(
            \Packages\Product\Services\ProductCategoryServiceInterface::class,
            \Packages\Product\Services\ProductCategoryService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Load routes (only if files exist)
        if (file_exists(__DIR__ . '/routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        }
        if (file_exists(__DIR__ . '/routes/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        }

        // Load views (only if directory exists)
        if (is_dir(__DIR__ . '/resources/views')) {
            $this->loadViewsFrom(__DIR__ . '/resources/views', 'product');
        }

        // Publish assets
        if ($this->app->runningInConsole()) {
            if (is_dir(__DIR__ . '/resources/views')) {
                $this->publishes([
                    __DIR__ . '/resources/views' => resource_path('views/vendor/product'),
                ], 'product-views');
            }

            if (is_dir(__DIR__ . '/Database/Migrations')) {
                $this->publishes([
                    __DIR__ . '/Database/Migrations' => database_path('migrations'),
                ], 'product-migrations');
            }
        }
    }
}