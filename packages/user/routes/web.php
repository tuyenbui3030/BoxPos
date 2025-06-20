<?php

use Illuminate\Support\Facades\Route;
use Packages\User\Http\Controllers\UserController;
use Packages\User\Livewire\Login;
use Packages\User\Livewire\Register;
use Packages\User\Livewire\Dashboard;
use Packages\User\Livewire\ManageDevices;

/*
|--------------------------------------------------------------------------
| User Package Routes
|--------------------------------------------------------------------------
|
| All user-related routes including authentication, profile management,
| and user-specific features like dashboard and device management.
|
*/

// Guest-only authentication routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    
    // Password reset routes  
    Route::get('/forgot-password', function () {
        return view('user::auth.forgot-password');
    })->name('password.request');
    
    Route::post('/forgot-password', function () {
        return back()->with('status', 'Password reset link sent!');
    })->name('password.email');
});

// Authenticated user routes
Route::middleware(['auth'])->group(function () {
    // Logout
    Route::post('/logout', function () {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
    
    // User dashboard and core features
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/devices', ManageDevices::class)->name('devices');
    
    // Profile management routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [UserController::class, 'profile'])->name('show');
        Route::get('/edit', [UserController::class, 'profile'])->name('edit');
        Route::put('/', [UserController::class, 'updateProfile'])->name('update');
        Route::put('/password', [UserController::class, 'changePassword'])->name('password.change');
    });
});
