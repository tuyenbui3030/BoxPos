<?php

namespace Packages\SessionManager;

use Illuminate\Support\ServiceProvider;
use Packages\SessionManager\Http\Middleware\ExtendSessionOnActivity;

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
    }

    /**
     * Register middleware for Session Manager package.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        
        // Register only the simple session extension middleware
        $router->aliasMiddleware('session.extend', ExtendSessionOnActivity::class);
    }
}
