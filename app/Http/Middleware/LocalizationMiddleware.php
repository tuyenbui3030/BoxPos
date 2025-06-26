<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
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

        $locale = $urlLocale ?? $sessionLocale ?? $defaultLocale;

        // Validate locale
        $availableLocales = array_keys(config('app.available_locales', ['en' => []]));
        if (!in_array($locale, $availableLocales)) {
            $locale = $defaultLocale;
        }

        // If no URL locale but we have session locale, redirect to localized URL
        if (!$urlLocale && $sessionLocale && $sessionLocale !== $defaultLocale) {
            $path = $request->path();
            // Don't redirect API, assets, or already localized paths
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

        return $next($request);
    }


}
