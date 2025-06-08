<?php

use Illuminate\Support\Facades\Route;
use Packages\SessionManager\Http\Controllers\SessionController;

/*
|--------------------------------------------------------------------------
| Session Manager API Routes
|--------------------------------------------------------------------------
|
| API routes for session management functionality
|
*/

Route::middleware(['auth'])->prefix('api/session')->name('api.session.')->group(function () {
    // Heartbeat endpoint to keep session alive
    Route::post('/heartbeat', [SessionController::class, 'heartbeat'])->name('heartbeat');
    
    // Session information
    Route::get('/info', [SessionController::class, 'sessionInfo'])->name('info');
    
    // Infinite session management
    Route::post('/infinite/enable', [SessionController::class, 'enableInfiniteSession'])->name('infinite.enable');
    Route::post('/infinite/disable', [SessionController::class, 'disableInfiniteSession'])->name('infinite.disable');
});

// Debug routes (only in non-production environments)
if (!app()->isProduction()) {
    Route::middleware(['auth'])->prefix('debug/session')->name('debug.session.')->group(function () {
        Route::get('/info', [SessionController::class, 'sessionInfo'])->name('info');
    });
}
