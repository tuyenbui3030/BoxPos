<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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

        // Configure Livewire for localized routes
        $this->configureLivewireForLocalization();
    }

    /**
     * Configure Livewire to work with localized routes
     */
    protected function configureLivewireForLocalization(): void
    {
        // We'll use middleware approach instead of overriding routes
        // This is handled in LivewireLocalizationMiddleware
    }
}
