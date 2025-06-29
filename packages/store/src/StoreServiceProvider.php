<?php

namespace Packages\Store;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Packages\Store\Models\Store;
use Packages\Store\Repositories\StoreRepository;
use Packages\Store\Services\StoreService;

class StoreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository
        $this->app->bind(StoreRepository::class, function ($app) {
            return new StoreRepository($app->make(Store::class));
        });

        // Bind service
        $this->app->bind(StoreService::class, function ($app) {
            return new StoreService($app->make(StoreRepository::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'store');

        // Register commands
        if ($this->app->runningInConsole()) {
            $commands = [];

            // Only register debug commands in debug mode
            if (config('app.debug')) {
                $commands = [
                    \Packages\Store\Console\Commands\TestStoreSelection::class,
                    \Packages\Store\Console\Commands\TestStoreSwitching::class,
                    \Packages\Store\Console\Commands\DiagnoseStoreSwitching::class,
                ];
            }

            if (!empty($commands)) {
                $this->commands($commands);
            }
        }

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/store.php' => config_path('store.php'),
        ], 'store-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/Database/Migrations' => database_path('migrations'),
        ], 'store-migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/store'),
        ], 'store-views');

        // Register Livewire components
        $this->registerLivewireComponents();
    }

    /**
     * Register Livewire components.
     */
    protected function registerLivewireComponents(): void
    {
        Livewire::component('store-selection', \Packages\Store\Livewire\StoreSelection::class);
    }
}
