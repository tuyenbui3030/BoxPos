<?php

namespace Packages\CashManagement;

use Illuminate\Support\ServiceProvider;

class CashManagementServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register package services
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Publish migrations if needed
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Database/Migrations' => database_path('migrations'),
            ], 'cash-management-migrations');
        }
    }
}
