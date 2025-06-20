<?php

use Illuminate\Support\Facades\Route;
use Packages\Appearance\Livewire\ThemeSwitcher;

/*
|--------------------------------------------------------------------------
| Appearance Package Routes
|--------------------------------------------------------------------------
|
| Routes for appearance and theming functionality.
|
*/

// Theme switching routes (available to authenticated users)
Route::middleware(['auth'])->group(function () {
    // Theme switcher component is typically embedded, no direct route needed
    // But we can add theme API endpoints here if needed
    
    Route::post('/theme/switch', function () {
        $theme = request('theme', 'light');
        session(['theme' => $theme]);
        return response()->json(['success' => true, 'theme' => $theme]);
    })->name('theme.switch');
});
