<?php

namespace Packages\Tenant;

use Illuminate\Support\ServiceProvider;
use Packages\Tenant\Services\TenantService;
use Packages\Store\Services\StoreService;

class TenantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind tenant service
        $this->app->singleton(TenantService::class, function ($app) {
            return new TenantService($app->make(StoreService::class));
        });

        // Register facade
        $this->app->alias(TenantService::class, 'tenant');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register middleware
        $this->app['router']->aliasMiddleware('tenant.context', \Packages\Tenant\Middleware\TenantContext::class);
        $this->app['router']->aliasMiddleware('tenant.isolation', \Packages\Tenant\Middleware\TenantDataIsolation::class);

        // Register commands
        if ($this->app->runningInConsole()) {
            $commands = [
                \Packages\Tenant\Console\Commands\FixUserStoreRelationships::class,
            ];

            // Only register debug commands in debug mode
            if (config('app.debug')) {
                $commands[] = \Packages\Tenant\Console\Commands\CheckUserStoreAccess::class;
            }

            $this->commands($commands);
        }

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/tenant.php' => config_path('tenant.php'),
        ], 'tenant-config');
    }
}
