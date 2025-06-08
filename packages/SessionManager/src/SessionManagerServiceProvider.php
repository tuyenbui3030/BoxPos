<?php

namespace Packages\SessionManager;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Packages\SessionManager\Http\Middleware\KeepAliveSession;
use Packages\SessionManager\Http\Middleware\ExtendSessionOnActivity;
use Packages\SessionManager\Services\SessionManagerService;

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

        // Register services
        $this->app->singleton(SessionManagerService::class, function ($app) {
            return new SessionManagerService();
        });
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

        // Publish assets
        $this->publishes([
            __DIR__.'/../resources/js' => public_path('js/session-manager'),
        ], 'session-manager-assets');

        // Register middleware
        $this->registerMiddleware();

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Packages\SessionManager\Console\Commands\CleanupExpiredSessions::class,
            ]);
        }
    }

    /**
     * Register middleware for Session Manager package.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        
        // Register middleware aliases
        $router->aliasMiddleware('session.keep-alive', KeepAliveSession::class);
        $router->aliasMiddleware('session.extend', ExtendSessionOnActivity::class);
    }
}
