<?php

namespace Packages\Appearance;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppearanceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge package config
        $this->mergeConfigFrom(
            __DIR__.'/../config/appearance.php',
            'appearance'
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
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'appearance');
        
        // Publish config
        $this->publishes([
            __DIR__.'/../config/appearance.php' => config_path('appearance.php'),
        ], 'appearance-config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/appearance'),
        ], 'appearance-views');
    }

    /**
     * Register Livewire components from this package.
     */
    protected function registerLivewireComponents(): void
    {
        // Register appearance switcher components
        Livewire::component('appearance-switcher', \Packages\Appearance\Livewire\AppearanceSwitcher::class);
        Livewire::component('packages.appearance.livewire.appearance-switcher', \Packages\Appearance\Livewire\AppearanceSwitcher::class);
        
        // Keep backward compatibility aliases
        Livewire::component('theme-switcher', \Packages\Appearance\Livewire\AppearanceSwitcher::class);
        Livewire::component('packages.appearance.livewire.theme-switcher', \Packages\Appearance\Livewire\AppearanceSwitcher::class);
        
        // Register confirmation modal component
        Livewire::component('confirmation-modal', \Packages\Appearance\Livewire\ConfirmationModal::class);
        Livewire::component('packages.appearance.livewire.confirmation-modal', \Packages\Appearance\Livewire\ConfirmationModal::class);
    }

    /**
     * Register middleware for Appearance package.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        
        // Register middleware aliases
        $router->aliasMiddleware('appearance', \Packages\Appearance\Http\Middleware\AppearanceMiddleware::class);
        // Keep backward compatibility
        $router->aliasMiddleware('theme', \Packages\Appearance\Http\Middleware\AppearanceMiddleware::class);
    }
}
