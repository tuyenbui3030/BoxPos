<?php

namespace Packages\Localization;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class LocalizationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/localization.php', 'localization');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'localization');
        
        // Load routes
        $this->loadRoutes();
        
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/localization.php' => config_path('localization.php'),
        ], 'localization-config');
        
        // Publish translations
        $this->publishes([
            __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/localization'),
        ], 'localization-translations');
    }
    
    /**
     * Load package routes
     */
    protected function loadRoutes(): void
    {
        Route::group([
            'namespace' => 'Packages\Localization\Http\Controllers',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }
}
