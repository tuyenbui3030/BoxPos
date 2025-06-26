<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register main application routes. Package-specific
| routes are handled by their respective service providers.
|
*/

// Note: Language switching routes are now handled by Localization package

// Example localization page (for testing)
Route::get('/localization-example', function () {
    return view('localization-example');
})->name('localization.example');

// Test localization in project layout
Route::get('/localization-test', [TestController::class, 'localization'])
    ->name('localization.test');

// Language test page
Route::get('/language-test', function () {
    return view('language-test');
})->name('language.test');

// Localized routes group
Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => 'en|vi'],
    'middleware' => ['web']
], function () {

    // Home route redirects to dashboard
    Route::get('/', function ($locale) {
        if (auth()->check()) {
            return redirect("/$locale/dashboard");
        }
        return redirect("/$locale/login");
    })->name('locale.home');

    // Guest routes
    Route::middleware(['guest'])->group(function () {
        Route::get('/login', \Packages\User\Livewire\Login::class)->name('locale.login');
        Route::get('/register', \Packages\User\Livewire\Register::class)->name('locale.register');

        Route::get('/forgot-password', function () {
            return view('user::auth.forgot-password');
        })->name('locale.password.request');
    });

    // Authenticated routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', \Packages\User\Livewire\Dashboard::class)->name('locale.dashboard');
        Route::get('/devices', \Packages\User\Livewire\ManageDevices::class)->name('locale.devices');
        Route::get('/customers', \Packages\Customer\Livewire\CustomerManagement::class)->name('locale.customers');

        // Logout
        Route::post('/logout', function () {
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect()->route('locale.login', ['locale' => app()->getLocale()]);
        })->name('locale.logout');

        // Profile routes
        Route::prefix('profile')->name('locale.profile.')->group(function () {
            Route::get('/', [\Packages\User\Http\Controllers\UserController::class, 'profile'])->name('show');
            Route::get('/edit', [\Packages\User\Http\Controllers\UserController::class, 'profile'])->name('edit');
            Route::put('/', [\Packages\User\Http\Controllers\UserController::class, 'updateProfile'])->name('update');
            Route::put('/password', [\Packages\User\Http\Controllers\UserController::class, 'changePassword'])->name('password.change');
        });
    });
});

// Note: Package routes will still be available without locale prefix
// Fallback routes will redirect them to localized versions
