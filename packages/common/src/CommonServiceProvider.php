<?php

namespace Packages\Common;

use Illuminate\Support\ServiceProvider;
use Packages\Common\Services\LoggingService;
use Packages\Common\Services\StoreService;
use Packages\Common\Repositories\UserStoreRepository;

class CommonServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the logging service as singleton
        $this->app->singleton(LoggingService::class, function ($app) {
            return new LoggingService();
        });

        // Register the store service as singleton
        $this->app->singleton(StoreService::class, function ($app) {
            return new StoreService($app->make(UserStoreRepository::class));
        });

        // Register the user store repository
        $this->app->singleton(UserStoreRepository::class, function ($app) {
            return new UserStoreRepository();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register Blade service provider
        $this->app->register(\Packages\Common\Providers\BladeServiceProvider::class);

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/common.php' => config_path('common.php'),
        ], 'common-config');

        // Publish migrations if needed
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'common-migrations');
        }

        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/common.php', 'common');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Packages\Common\Console\Commands\CleanOldLogsCommand::class,
            ]);
        }
    }
}