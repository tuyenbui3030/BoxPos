<?php

namespace Packages\User;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Packages\User\Services\UserService;
use Packages\User\Repositories\UserRepository;
use Packages\User\Events\UserRegistered;
use Packages\User\Events\UserLoggedIn;
use Packages\User\Events\UserPasswordChanged;
use Packages\User\Listeners\SendWelcomeEmail;
use Packages\User\Listeners\LogUserLogin;
use Packages\User\Listeners\NotifyPasswordChange;

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
        
        // Register services
        $this->app->singleton(UserService::class, function ($app) {
            return new UserService($app->make(UserRepository::class));
        });
        
        // Register repositories
        $this->app->singleton(UserRepository::class, function ($app) {
            return new UserRepository($app->make(\Packages\User\Models\User::class));
        });
        
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/user.php',
            'user'
        );
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
        
        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'user');
        
        // Publish migrations
        $this->publishes([
            __DIR__ . '/Database/Migrations' => database_path('migrations'),
        ], 'user-migrations');
        
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/user.php' => config_path('user.php'),
        ], 'user-config');
        
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

    /**
     * Register event listeners for User package.
     */
    protected function registerEventListeners(): void
    {
        Event::listen(UserRegistered::class, SendWelcomeEmail::class);
        Event::listen(UserLoggedIn::class, LogUserLogin::class);
        Event::listen(UserPasswordChanged::class, NotifyPasswordChange::class);
    }

    /**
     * Register middleware for User package.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        
        // Register middleware aliases
        $router->aliasMiddleware('user.access', \Packages\User\Http\Middleware\EnsureUserAccess::class);
        $router->aliasMiddleware('user.auth.log', \Packages\User\Http\Middleware\LogUserAuthentication::class);
        $router->aliasMiddleware('user.auth.rate.limit', \Packages\User\Http\Middleware\AuthenticationRateLimiter::class);
        $router->aliasMiddleware('user.password.validate', \Packages\User\Http\Middleware\ValidateCurrentPassword::class);
        $router->aliasMiddleware('user.authenticated', \Packages\User\Http\Middleware\EnsureUserIsAuthenticated::class);
        $router->aliasMiddleware('user.guest', \Packages\User\Http\Middleware\RedirectIfAuthenticated::class);
    }
}
