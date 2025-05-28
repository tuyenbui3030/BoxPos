<?php

use App\Livewire\Dashboard;
use App\Livewire\Login;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', Login::class)->name('login');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/devices', App\Livewire\ManageDevices::class)->name('devices');
    
    // Add more authenticated routes here
});

// Auth routes
Route::get('/register', App\Livewire\Register::class)->name('register')
    ->middleware('guest');

// Password reset routes
Route::get('/forgot-password', [App\Http\Controllers\Auth\PasswordResetController::class, 'create'])
    ->name('password.request')
    ->middleware('guest');

Route::post('/forgot-password', [App\Http\Controllers\Auth\PasswordResetController::class, 'store'])
    ->name('password.email')
    ->middleware('guest');

Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\NewPasswordController::class, 'create'])
    ->name('password.reset')
    ->middleware('guest');

Route::post('/reset-password', [App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
    ->name('password.update')
    ->middleware('guest');

Route::post('/logout', function () {
    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');
