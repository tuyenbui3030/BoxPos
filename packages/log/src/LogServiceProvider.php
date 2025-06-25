<?php

namespace Packages\Log;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Packages\Log\Services\LogService;
use Packages\Log\Services\LogFormatterService;
use Packages\Log\Services\QueryPerformanceService;
use Packages\Log\Services\SentryService;
use Packages\Log\Middleware\LogRequests;
use Packages\Log\Middleware\LogSqlQueries;
use Packages\Log\Middleware\LogPerformance;
use Packages\Log\Middleware\SentryPerformanceMiddleware;
use Packages\Log\Channels\RemoteFileChannel;
use Packages\Log\Channels\SlackChannel;
use Packages\Log\Console\TestSentryCommand;

class LogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge package configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/logging.php',
            'logging-package'
        );

        // Register services
        $this->registerServices();
        
        // Register custom log channels
        $this->registerLogChannels();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/logging.php' => config_path('logging-package.php'),
        ], 'log-config');

        // Register middleware
        $this->registerMiddleware();

        // Set up SQL query listeners
        $this->setupSqlQueryListeners();

        // Set up log cleanup scheduler
        $this->setupLogCleanup();

        // Register console commands
        $this->registerCommands();
    }

    /**
     * Register console commands
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TestSentryCommand::class,
            ]);
        }
    }

    /**
     * Register package services.
     */
    protected function registerServices(): void
    {
        $this->app->singleton(LogService::class, function ($app) {
            return new LogService(
                $app->make(LogFormatterService::class),
                $app->make(QueryPerformanceService::class)
            );
        });

        $this->app->singleton(LogFormatterService::class, function ($app) {
            return new LogFormatterService();
        });

        $this->app->singleton(QueryPerformanceService::class, function ($app) {
            return new QueryPerformanceService();
        });

        $this->app->singleton(SentryService::class, function ($app) {
            return new SentryService();
        });

        // Bind to shorter aliases for easier access
        $this->app->alias(LogService::class, 'log.service');
        $this->app->alias(LogFormatterService::class, 'log.formatter');
        $this->app->alias(QueryPerformanceService::class, 'log.query-performance');
        $this->app->alias(SentryService::class, 'log.sentry');
    }

    /**
     * Register custom log channels.
     */
    protected function registerLogChannels(): void
    {
        $this->app->make('log')->extend('remote_file', function ($app, $config) {
            return new RemoteFileChannel($config);
        });

        $this->app->make('log')->extend('slack_critical', function ($app, $config) {
            return new SlackChannel($config);
        });
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];

        $router->aliasMiddleware('log.requests', LogRequests::class);
        $router->aliasMiddleware('log.sql', LogSqlQueries::class);
        $router->aliasMiddleware('log.performance', LogPerformance::class);
        $router->aliasMiddleware('sentry.performance', SentryPerformanceMiddleware::class);
    }

    /**
     * Set up SQL query listeners for automatic query logging.
     */
    protected function setupSqlQueryListeners(): void
    {
        if (!config('logging-package.sql.enabled', false)) {
            return;
        }

        // Listen for all database queries
        DB::listen(function ($query) {
            $logService = $this->app->make(LogService::class);
            $logService->logSqlQuery(
                $query->sql,
                $query->time,
                $query->bindings
            );
        });

        // Set up query performance tracking
        if (config('logging-package.sql.detect_n_plus_one', true)) {
            $this->setupNPlusOneDetection();
        }
    }

    /**
     * Set up N+1 query detection.
     */
    protected function setupNPlusOneDetection(): void
    {
        $queryPerformanceService = $this->app->make(QueryPerformanceService::class);
        
        // Track queries per request
        $this->app->booted(function () use ($queryPerformanceService) {
            $queryPerformanceService->startRequestTracking();
        });

        // Analyze queries at the end of request
        $this->app->terminating(function () use ($queryPerformanceService) {
            $queryPerformanceService->analyzeRequestQueries();
        });
    }

    /**
     * Set up automatic log cleanup.
     */
    protected function setupLogCleanup(): void
    {
        if (!config('logging-package.file_management.cleanup_enabled', true)) {
            return;
        }

        // Register cleanup command
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Packages\Log\Console\Commands\CleanupLogsCommand::class,
            ]);
        }

        // Schedule cleanup if using Laravel's scheduler
        if ($this->app->runningInConsole()) {
            $this->setupScheduledCleanup();
        }
    }

    /**
     * Set up scheduled log cleanup.
     */
    protected function setupScheduledCleanup(): void
    {
        // This would be handled by Laravel's task scheduler
        // Users need to add to their Kernel.php schedule method
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            LogService::class,
            LogFormatterService::class,
            QueryPerformanceService::class,
            SentryService::class,
            'log.service',
            'log.formatter',
            'log.query-performance',
            'log.sentry',
        ];
    }
}
