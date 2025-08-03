<?php

namespace Packages\Common\Http;

use Packages\Common\Middleware\StoreContextMiddleware;

class Kernel
{
    /**
     * The application's route middleware.
     */
    protected $routeMiddleware = [
        'store.context' => StoreContextMiddleware::class,
    ];

    /**
     * Get the route middleware for registration
     */
    public static function getRouteMiddleware(): array
    {
        return [
            'store.context' => StoreContextMiddleware::class,
        ];
    }
}