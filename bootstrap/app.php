<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'guest' => \Packages\User\Http\Middleware\RedirectIfAuthenticated::class,
            'extend.session' => \Packages\SessionManager\Http\Middleware\ExtendSessionOnActivity::class,
            'localization' => \App\Http\Middleware\LocalizationMiddleware::class,
            // Log package middleware aliases
            'log.requests' => \Packages\Log\Middleware\LogRequests::class,
            'log.sql' => \Packages\Log\Middleware\LogSqlQueries::class,
            'log.performance' => \Packages\Log\Middleware\LogPerformance::class,
            'sentry.performance' => \Packages\Log\Middleware\SentryPerformanceMiddleware::class,
        ]);
        
        // Add middleware to web group
        $middleware->web(append: [
            \App\Http\Middleware\LocalizationMiddleware::class,
            \Packages\Appearance\Http\Middleware\AppearanceMiddleware::class,
            \Packages\SessionManager\Http\Middleware\ExtendSessionOnActivity::class,
            // Add Log performance middleware to all web requests
            \Packages\Log\Middleware\LogPerformance::class,
            // Add Sentry performance middleware for production monitoring
            \Packages\Log\Middleware\SentryPerformanceMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
