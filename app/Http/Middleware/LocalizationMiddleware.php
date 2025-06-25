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
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the locale from URL parameter, session, or default
        $locale = $this->getLocale($request);
        
        // Set the application locale
        App::setLocale($locale);
        
        // Store locale in session for persistence
        Session::put('locale', $locale);
        
        return $next($request);
    }
    
    /**
     * Get the locale from various sources
     */
    private function getLocale(Request $request): string
    {
        $supportedLocales = ['en', 'vi'];
        $defaultLocale = config('app.locale', 'en');
        
        // Check if locale is set via URL parameter
        if ($request->has('lang') && in_array($request->get('lang'), $supportedLocales)) {
            return $request->get('lang');
        }
        
        // Check if locale is set via route parameter
        if ($request->route('locale') && in_array($request->route('locale'), $supportedLocales)) {
            return $request->route('locale');
        }
        
        // Check session
        if (Session::has('locale') && in_array(Session::get('locale'), $supportedLocales)) {
            return Session::get('locale');
        }
        
        // Check browser preference
        $preferredLanguage = $request->getPreferredLanguage($supportedLocales);
        if ($preferredLanguage && in_array($preferredLanguage, $supportedLocales)) {
            return $preferredLanguage;
        }
        
        return $defaultLocale;
    }
}
