<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class LocalizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register custom Blade directives for localization
        $this->registerBladeDirectives();
    }

    /**
     * Register custom Blade directives
     */
    private function registerBladeDirectives(): void
    {
        // @translation directive for quick translations
        Blade::directive('translation', function ($expression) {
            return "<?php echo __($expression); ?>";
        });

        // @currentLang directive to get current language code
        Blade::directive('currentLang', function () {
            return "<?php echo app()->getLocale(); ?>";
        });

        // @isLang directive to check current language
        Blade::directive('isLang', function ($expression) {
            return "<?php if(app()->getLocale() === $expression): ?>";
        });

        // @endisLang directive
        Blade::directive('endisLang', function () {
            return "<?php endif; ?>";
        });

        // @langDirection directive to get language direction
        Blade::directive('langDirection', function () {
            return "<?php echo get_language_direction(); ?>";
        });
    }
}
