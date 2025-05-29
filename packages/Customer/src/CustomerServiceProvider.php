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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Livewire components
        $this->registerLivewireComponents();
        
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        
        // Load routes
        $this->loadRoutes();
        
        // Load views if needed (commented out - using main app views for now)
        // $this->loadViewsFrom(__DIR__ . '/../resources/views', 'customer');
        
        // Publish migrations
        $this->publishes([
            __DIR__ . '/Database/Migrations' => database_path('migrations'),
        ], 'customer-migrations');
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
}
