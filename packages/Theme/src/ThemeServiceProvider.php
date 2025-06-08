<?php

namespace Packages\Theme;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge package config
        $this->mergeConfigFrom(
            __DIR__.'/../config/theme.php',
            'theme'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Livewire components
        $this->registerLivewireComponents();
        
        // Register middleware
        $this->registerMiddleware();
        
        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'theme');
        
        // Publish config
        $this->publishes([
            __DIR__.'/../config/theme.php' => config_path('theme.php'),
        ], 'theme-config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/theme'),
        ], 'theme-views');
    }

    /**
     * Register Livewire components from this package.
     */
    protected function registerLivewireComponents(): void
    {
        Livewire::component('theme-switcher', \Packages\Theme\Livewire\ThemeSwitcher::class);
        Livewire::component('packages.theme.livewire.theme-switcher', \Packages\Theme\Livewire\ThemeSwitcher::class);
    }

    /**
     * Register middleware for Theme package.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        
        // Register middleware aliases
        $router->aliasMiddleware('theme', \Packages\Theme\Http\Middleware\ThemeMiddleware::class);
    }
}
