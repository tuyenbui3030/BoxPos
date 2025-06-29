<?php

namespace Packages\Localization\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Routing\Controller;
use Packages\Log\Traits\Loggable;

class LanguageController extends Controller
{
    use Loggable; // ⚠️ MANDATORY: Use Loggable trait
    /**
     * Switch the application language
     */
    public function switch(Request $request, string $locale)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('language_switch_requested', [
            'from_locale' => app()->getLocale(),
            'to_locale' => $locale,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Get available locales from config
        $availableLocales = config('app.available_locales', []);

        // Validate locale
        if (!array_key_exists($locale, $availableLocales)) {
            // ⚠️ MANDATORY: Log error
            $this->logActivity('language_switch_failed', [
                'locale' => $locale,
                'reason' => 'unsupported_locale',
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => __('localization::language.unsupported_locale')
                ], 400);
            }

            return redirect()->back()->with('error', __('localization::language.unsupported_locale'));
        }

        try {
            // Set the locale in session and application
            Session::put('app_locale', $locale);
            App::setLocale($locale);

            // ⚠️ MANDATORY: Log successful switch
            $this->logActivity('language_switched_successfully', [
                'new_locale' => $locale,
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
            ]);

            // Return JSON response for AJAX calls
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'locale' => $locale,
                    'message' => __('localization::language.switched_successfully')
                ]);
            }

            // For web requests, redirect to localized URL
            $currentUrl = $request->header('referer', '/');
            $localizedUrl = $this->getLocalizedUrl($currentUrl, $locale);

            // Debug logging
            \Log::info('Language switch debug', [
                'locale' => $locale,
                'current_url' => $currentUrl,
                'localized_url' => $localizedUrl,
                'session_locale' => Session::get('app_locale'),
                'app_locale' => App::getLocale()
            ]);

            // Check if this is a Livewire request (SPA navigation)
            if ($request->hasHeader('X-Livewire')) {
                return response()->json([
                    'success' => true,
                    'locale' => $locale,
                    'redirect' => $localizedUrl,
                    'message' => __('localization::language.switched_successfully')
                ]);
            }

            return redirect($localizedUrl)
                ->with('success', __('localization::language.switched_successfully'));

        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'LanguageController',
                'action' => 'switch',
                'locale' => $locale,
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => __('localization::language.switch_failed')
                ], 500);
            }

            return redirect()->back()->with('error', __('localization::language.switch_failed'));
        }
    }

    /**
     * Get available languages from config
     */
    public function getAvailableLanguages(): array
    {
        return config('app.available_locales', [
            'en' => [
                'name' => 'English',
                'native' => 'English',
                'flag' => '🇺🇸',
                'direction' => 'ltr'
            ],
            'vi' => [
                'name' => 'Vietnamese',
                'native' => 'Tiếng Việt',
                'flag' => '🇻🇳',
                'direction' => 'ltr'
            ]
        ]);
    }

    /**
     * Get localized URL by adding locale prefix
     */
    private function getLocalizedUrl(string $url, string $locale): string
    {
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '/';

        // Remove existing locale prefix if any
        $availableLocales = array_keys($this->getAvailableLanguages());
        foreach ($availableLocales as $existingLocale) {
            if (str_starts_with($path, "/$existingLocale/") || $path === "/$existingLocale") {
                $path = substr($path, strlen("/$existingLocale"));
                break;
            }
        }

        // Ensure path starts with /
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        // Add new locale prefix
        $localizedPath = "/$locale" . $path;

        // Rebuild URL
        $scheme = $parsedUrl['scheme'] ?? 'http';
        $host = $parsedUrl['host'] ?? request()->getHost();
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';

        return "$scheme://$host$port$localizedPath";
    }

    /**
     * Get current language
     */
    public function getCurrentLanguage(): array
    {
        $currentLocale = App::getLocale();
        $languages = $this->getAvailableLanguages();

        return $languages[$currentLocale] ?? $languages['en'];
    }

    /**
     * Get translations for a specific locale
     */
    public function getTranslations(string $locale)
    {
        $supportedLocales = config('app.available_locales', ['en' => 'English', 'vi' => 'Tiếng Việt']);

        if (!array_key_exists($locale, $supportedLocales)) {
            return response()->json(['error' => 'Unsupported locale'], 400);
        }

        // Set locale temporarily to get translations
        $originalLocale = App::getLocale();
        App::setLocale($locale);

        try {
            $translations = [
                'app' => trans('app'),
                'user' => trans('user::user'),
                'customer' => trans('customer::customer'),
                'localization' => trans('localization::language'),
            ];

            return response()->json([
                'success' => true,
                'locale' => $locale,
                'translations' => $translations
            ]);
        } finally {
            // Restore original locale
            App::setLocale($originalLocale);
        }
    }
}
