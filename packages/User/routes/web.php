<?php

use Illuminate\Support\Facades\Route;
use Packages\User\Http\Controllers\UserController;
use Packages\User\Livewire\Login;
use Packages\User\Livewire\Register;

// Protected user routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [UserController::class, 'changePassword'])->name('password.change');
});

// API routes for User package
Route::middleware(['auth:api'])->prefix('api/user')->name('api.user.')->group(function () {
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::put('/password', [UserController::class, 'changePassword'])->name('password.change');
});

// Logout route
Route::post('/logout', function () {
    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// Password reset routes (temporarily moved here for testing)
Route::get('/forgot-password', function() {
    return 'Password reset page working!';
})->name('password.request')->middleware('guest');

// User-related authenticated routes
Route::middleware(['auth'])->group(function () {
    // Add user-specific routes here
});
