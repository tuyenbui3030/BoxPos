<?php

use Illuminate\Support\Facades\Route;
use Packages\User\Http\Controllers\UserController;
use Packages\User\Livewire\Login;
use Packages\User\Livewire\Register;

// Authentication routes
Route::get('/login', Login::class)->name('login')->middleware('guest');
Route::get('/register', Register::class)->name('register')->middleware('guest');

// Protected user routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [UserController::class, 'changePassword'])->name('password.change');
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
