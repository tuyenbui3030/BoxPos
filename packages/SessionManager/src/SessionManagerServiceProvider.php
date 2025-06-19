<?php

namespace Packages\SessionManager;

use Illuminate\Support\ServiceProvider;
use Packages\SessionManager\Http\Middleware\ExtendSessionOnActivity;
use Packages\SessionManager\Services\SessionService;

class SessionManagerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge package config
        $this->mergeConfigFrom(
            __DIR__.'/../config/session-manager.php',
            'session-manager'
        );

        // Register SessionService
        $this->app->singleton(SessionService::class, function ($app) {
            return new SessionService();
        });

        // Bind to shorter alias for easier access
        $this->app->alias(SessionService::class, 'session.manager');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/session-manager.php' => config_path('session-manager.php'),
        ], 'session-manager-config');

        // Register middleware
        $this->registerMiddleware();

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Packages\SessionManager\Console\Commands\CleanupExpiredSessions::class,
            ]);
        }

        // Load routes if they exist
        $this->loadRoutes();
    }

    /**
     * Register middleware for Session Manager package.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        
        // Register middleware aliases
        $router->aliasMiddleware('session.extend', ExtendSessionOnActivity::class);
        
        // Register logging middleware if Log package is available
        if (class_exists(\Packages\Log\Middleware\LogRequests::class)) {
            $router->aliasMiddleware('session.log.requests', \Packages\Log\Middleware\LogRequests::class);
            $router->aliasMiddleware('session.log.performance', \Packages\Log\Middleware\LogPerformance::class);
        }
    }

    /**
     * Load package routes.
     */
    protected function loadRoutes(): void
    {
        if (file_exists(__DIR__.'/../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            SessionService::class,
            'session.manager',
        ];
    }
}
