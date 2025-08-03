<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Packages\User\Http\Middleware\EnsureUserIsAuthenticated;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Regular web routes (packages will register here)
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Fallback routes (redirect to localized) - loaded last
            Route::middleware('web')
                ->group(base_path('routes/fallback.php'));
        });

        // Register our custom middleware
        Route::aliasMiddleware('auth', EnsureUserIsAuthenticated::class);
    }
}
