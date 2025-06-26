<?php

namespace Packages\Customer;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

class CustomerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register aliases for backward compatibility
        $this->app->alias(\Packages\Customer\Models\Customer::class, \App\Models\Customer::class);

        // Register services
        $this->app->bind(
            \Packages\Customer\Repositories\CustomerRepository::class,
            \Packages\Customer\Repositories\CustomerRepository::class
        );

        $this->app->bind(
            \Packages\Customer\Services\CustomerService::class,
            \Packages\Customer\Services\CustomerService::class
        );

        // Register configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/customer.php', 'customer');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Livewire components
        $this->registerLivewireComponents();

        // Register middleware
        $this->registerMiddleware();

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Load routes
        $this->loadRoutes();

        // Load views from package
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'customer');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'customer');

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/customer.php' => config_path('customer.php'),
        ], 'customer-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/Database/Migrations' => database_path('migrations'),
        ], 'customer-migrations');

        // Publish translations
        $this->publishes([
            __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/customer'),
        ], 'customer-translations');

        // Register event listeners
        $this->registerEventListeners();
    }

    /**
     * Load package routes.
     */
    protected function loadRoutes(): void
    {
        Route::group([
            'middleware' => ['web'],
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
     * Register Livewire components from this package.
     */
    protected function registerLivewireComponents(): void
    {
        // Register Customer package Livewire components
        Livewire::component('customer-management', \Packages\Customer\Livewire\CustomerManagement::class);
        Livewire::component('create-customer', \Packages\Customer\Livewire\CreateCustomer::class);
        Livewire::component('customer-table', \Packages\Customer\Livewire\CustomerTable::class);
    }

    /**
     * Register event listeners for this package.
     */
    protected function registerEventListeners(): void
    {
        // Register event listeners
        $this->app['events']->listen(
            \Packages\Customer\Events\CustomerCreated::class,
            \Packages\Customer\Listeners\SendWelcomeEmail::class
        );

        $this->app['events']->listen(
            \Packages\Customer\Events\CustomerUpdated::class,
            \Packages\Customer\Listeners\LogCustomerUpdate::class
        );
    }

    /**
     * Register middleware for Customer package.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];

        // Register middleware aliases
        $router->aliasMiddleware('customer.ownership', \Packages\Customer\Http\Middleware\EnsureCustomerOwnership::class);
        $router->aliasMiddleware('customer.logging', \Packages\Customer\Http\Middleware\LogCustomerActions::class);
        $router->aliasMiddleware('customer.rate.limit', \Packages\Customer\Http\Middleware\CustomerRateLimiter::class);
    }
}
