<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;
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

// Language switching routes
Route::get('/language/{locale}', [LanguageController::class, 'switch'])
    ->name('language.switch')
    ->where('locale', 'en|vi');

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

// Root redirect
Route::get('/', function () {
    return redirect()->route('login');
});

// Main authenticated application routes
Route::middleware(['auth'])->group(function () {
    // Add other main application routes here that don't belong to specific packages
    // Individual package routes are handled by their respective service providers
});
