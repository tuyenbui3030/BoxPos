<?php

namespace Packages\User;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register aliases for backward compatibility
        $this->app->alias(\Packages\User\Models\User::class, \App\Models\User::class);
        $this->app->alias(\Packages\User\Models\UserDevice::class, \App\Models\UserDevice::class);
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
        // $this->loadViewsFrom(__DIR__ . '/../resources/views', 'user');
        
        // Publish migrations
        $this->publishes([
            __DIR__ . '/Database/Migrations' => database_path('migrations'),
        ], 'user-migrations');
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

        Route::group([
            'middleware' => ['web'],
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/auth.php');
        });
    }

    /**
     * Register Livewire components from this package.
     */
    protected function registerLivewireComponents(): void
    {
        // Register User package Livewire components with both naming conventions
        Livewire::component('login', \Packages\User\Livewire\Login::class);
        Livewire::component('register', \Packages\User\Livewire\Register::class);
        Livewire::component('packages.user.livewire.login', \Packages\User\Livewire\Login::class);
        Livewire::component('packages.user.livewire.register', \Packages\User\Livewire\Register::class);
    }
}
