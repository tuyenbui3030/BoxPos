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
            'keep.alive' => \Packages\SessionManager\Http\Middleware\KeepAliveSession::class,
        ]);
        
        // Add theme middleware to web group (SessionManager temporarily disabled)
        $middleware->web(append: [
            \App\Http\Middleware\ThemeMiddleware::class,
            // \Packages\SessionManager\Http\Middleware\KeepAliveSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
