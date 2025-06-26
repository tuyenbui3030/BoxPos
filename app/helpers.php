<?php

if (!function_exists('get_current_language')) {
    /**
     * Get current language information
     */
    function get_current_language(): array
    {
        $controller = new \Packages\Localization\Http\Controllers\LanguageController();
        return $controller->getCurrentLanguage();
    }
}

if (!function_exists('get_available_languages')) {
    /**
     * Get all available languages
     */
    function get_available_languages(): array
    {
        $controller = new \Packages\Localization\Http\Controllers\LanguageController();
        return $controller->getAvailableLanguages();
    }
}

if (!function_exists('language_url')) {
    /**
     * Generate URL for language switching
     */
    function language_url(string $locale): string
    {
        return route('language.switch', ['locale' => $locale]);
    }
}

if (!function_exists('is_current_language')) {
    /**
     * Check if given locale is current language
     */
    function is_current_language(string $locale): bool
    {
        return app()->getLocale() === $locale;
    }
}

if (!function_exists('get_language_direction')) {
    /**
     * Get language direction (rtl or ltr)
     */
    function get_language_direction(): string
    {
        $rtlLanguages = ['ar', 'he', 'fa', 'ur'];
        $currentLocale = app()->getLocale();

        return in_array($currentLocale, $rtlLanguages) ? 'rtl' : 'ltr';
    }
}
