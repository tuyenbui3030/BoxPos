<?php

use Illuminate\Support\Facades\Route;
use Packages\Localization\Http\Controllers\LanguageController;

/*
|--------------------------------------------------------------------------
| Localization Package Routes
|--------------------------------------------------------------------------
|
| Here are the routes for language switching and localization features.
|
*/

// Language switching routes
Route::get('/language/{locale}', [LanguageController::class, 'switch'])
    ->name('language.switch')
    ->where('locale', 'en|vi');




