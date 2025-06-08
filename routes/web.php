<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register main application routes. Package-specific
| routes are handled by their respective service providers.
|
*/

// Root redirect
Route::get('/', function () {
    return redirect()->route('login');
});

// Main authenticated application routes
Route::middleware(['auth'])->group(function () {
    // Add other main application routes here that don't belong to specific packages
    // Individual package routes are handled by their respective service providers
});
