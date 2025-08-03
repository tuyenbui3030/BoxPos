<?php

namespace Packages\Notifications;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Packages\Notifications\Livewire\NotificationCenter;
use Packages\Notifications\Services\NotificationService;
use Packages\Notifications\Repositories\NotificationRepository;

class NotificationsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register repository
        $this->app->bind(NotificationRepository::class);
        
        // Register service
        $this->app->bind(NotificationService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'notifications');
        
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Register Livewire components
        Livewire::component('notification-center', NotificationCenter::class);

        // Publish migrations and seeders if needed
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Database/Migrations' => database_path('migrations'),
            ], 'notifications-migrations');

            $this->publishes([
                __DIR__ . '/Database/Seeders' => database_path('seeders'),
            ], 'notifications-seeders');
            
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/notifications'),
            ], 'notifications-views');
        }
    }
}
