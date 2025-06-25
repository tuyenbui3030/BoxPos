<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class LanguageController extends Controller
{
    /**
     * Switch the application language
     */
    public function switch(Request $request, string $locale)
    {
        $supportedLocales = ['en', 'vi'];
        
        // Validate locale
        if (!in_array($locale, $supportedLocales)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unsupported locale'], 400);
            }
            abort(400, 'Unsupported locale');
        }
        
        // Set the locale
        App::setLocale($locale);
        Session::put('locale', $locale);
        
        // Return JSON response for AJAX calls
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'locale' => $locale,
                'message' => __('app.language_switched_successfully')
            ]);
        }
        
        // Redirect back for non-AJAX requests
        return Redirect::back()->with('success', __('app.language') . ' ' . __('app.data_updated'));
    }
    
    /**
     * Get available languages
     */
    public function getAvailableLanguages(): array
    {
        return [
            'en' => [
                'code' => 'en',
                'name' => 'English',
                'native' => 'English',
                'flag' => '🇺🇸',
            ],
            'vi' => [
                'code' => 'vi',
                'name' => 'Vietnamese',
                'native' => 'Tiếng Việt',
                'flag' => '🇻🇳',
            ],
        ];
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
}
