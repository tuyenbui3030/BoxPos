<?php

namespace Packages\Reports;

use Illuminate\Support\ServiceProvider;

class ReportsServiceProvider extends ServiceProvider
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

        // Publish migrations and seeders if needed
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Database/Migrations' => database_path('migrations'),
            ], 'reports-migrations');

            $this->publishes([
                __DIR__ . '/Database/Seeders' => database_path('seeders'),
            ], 'reports-seeders');
        }
    }
}
