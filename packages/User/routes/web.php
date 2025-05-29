<?php

use Illuminate\Support\Facades\Route;
use Packages\User\Livewire\Login;
use Packages\User\Livewire\Register;

// Auth routes (guest only)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
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
