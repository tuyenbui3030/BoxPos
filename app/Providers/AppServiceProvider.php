<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load locale helpers
        require_once app_path('Helpers/LocaleHelper.php');

        // Set default Tabler UI pagination views for Laravel
        \Illuminate\Pagination\Paginator::useBootstrap();

        // Share theme with all views
        view()->composer('*', function ($view) {
            $view->with('currentTheme', session('theme', 'light'));
        });
    }
}
