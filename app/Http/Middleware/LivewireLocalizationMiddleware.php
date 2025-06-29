<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LivewireLocalizationMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this is a Livewire update request
        if ($request->is('livewire/update') && $request->isMethod('POST')) {
            // Get locale from referer URL first (most reliable), then session, then default
            $locale = null;

            $referer = $request->header('referer');
            if ($referer && preg_match('/\/(en|vi)\//', $referer, $matches)) {
                $locale = $matches[1];
            }

            // Fallback to session locale
            if (!$locale) {
                $locale = session('app_locale', 'en');
            }

            // Set locale context for this request
            app()->setLocale($locale);
            session(['app_locale' => $locale]);
        }

        return $next($request);
    }
}
