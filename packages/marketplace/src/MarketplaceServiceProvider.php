<?php

namespace Packages\Marketplace;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class MarketplaceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register services
        $this->app->singleton(
            \Packages\Marketplace\Services\MarketplaceService::class
        );
        
        $this->app->singleton(
            \Packages\Marketplace\Services\SubscriptionService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load package routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        
        // Load package views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'marketplace');
        
        // Load package migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        
        // Load package config
        $this->mergeConfigFrom(__DIR__.'/../config/marketplace.php', 'marketplace');
        
        // Register Livewire components
        $this->registerLivewireComponents();
        
        // Publish package assets
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/marketplace.php' => config_path('marketplace.php'),
            ], 'marketplace-config');
            
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/marketplace'),
            ], 'marketplace-views');
        }
    }

    /**
     * Register Livewire components
     */
    private function registerLivewireComponents(): void
    {
        if (class_exists(\Livewire\Livewire::class)) {
            // Register with full namespace
            \Livewire\Livewire::component('marketplace.app-switcher', \Packages\Marketplace\Livewire\AppSwitcher::class);
            
            // Also register with simple name for easier access
            \Livewire\Livewire::component('app-switcher', \Packages\Marketplace\Livewire\AppSwitcher::class);
        }
    }
}