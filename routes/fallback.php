<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Fallback Routes
|--------------------------------------------------------------------------
|
| These routes handle non-localized URLs and redirect to localized versions
|
*/

// Root redirect to localized home
Route::get('/', function () {
    $locale = session('app_locale', config('app.locale', 'en'));
    return redirect("/$locale");
})->name('home');

// Language switching route (non-localized)
Route::get('/language/{locale}', [\Packages\Localization\Http\Controllers\LanguageController::class, 'switch'])
    ->name('language.switch')
    ->where('locale', '[a-z]{2}');

// Redirect common routes to localized versions
$commonRoutes = ['dashboard', 'devices', 'customers', 'login', 'register', 'profile'];

foreach ($commonRoutes as $route) {
    Route::get("/$route", function () use ($route) {
        $locale = session('app_locale', config('app.locale', 'en'));
        return redirect("/$locale/$route");
    });
}

// Catch-all fallback for other non-localized routes
Route::fallback(function () {
    $locale = session('app_locale', config('app.locale', 'en'));
    $path = request()->path();

    // Don't redirect API routes, assets, or special routes
    if (str_starts_with($path, 'api/') ||
        str_starts_with($path, 'livewire/') ||
        str_starts_with($path, 'language/') ||
        str_contains($path, '.')) {
        abort(404);
    }

    return redirect("/$locale/$path");
});
