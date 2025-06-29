<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from URL parameter first, then session, then default
        $urlLocale = $request->route('locale');
        $sessionLocale = Session::get('app_locale');
        $defaultLocale = config('app.locale', 'en');

        // Priority: URL locale > session locale > default locale
        $locale = $urlLocale ?? $sessionLocale ?? $defaultLocale;

        // Validate locale
        $availableLocales = array_keys(config('app.available_locales', ['en' => []]));
        if (!in_array($locale, $availableLocales)) {
            $locale = $defaultLocale;
        }

        // If no URL locale but we have session locale, redirect to localized URL
        if (!$urlLocale && $sessionLocale && $sessionLocale !== $defaultLocale) {
            $path = $request->path();
            // Don't redirect API, assets, Livewire, or already localized paths
            if (!str_starts_with($path, 'api/') &&
                !str_starts_with($path, 'livewire/') &&
                !str_starts_with($path, 'language/') &&
                !str_contains($path, '.') &&
                !preg_match('/^(en|vi)\//', $path)) {
                return redirect("/$sessionLocale/$path");
            }
        }

        // Set application locale and store in session
        App::setLocale($locale);
        Session::put('app_locale', $locale);

        // Debug logging
        Log::info('LocalizationMiddleware debug', [
            'url_locale' => $urlLocale,
            'session_locale' => $sessionLocale,
            'final_locale' => $locale,
            'request_path' => $request->path(),
            'request_url' => $request->url()
        ]);

        return $next($request);
    }


}
