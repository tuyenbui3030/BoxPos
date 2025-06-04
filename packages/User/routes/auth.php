<?php

use Illuminate\Support\Facades\Route;
use Packages\User\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| These routes handle user authentication including login, registration,
| logout, and password management. They use the UserController with
| proper middleware for security.
|
*/

// Guest-only authentication routes
Route::middleware(['guest', 'user.auth.rate.limit:5,1'])->group(function () {
    // Login routes
    Route::get('/login', [UserController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [UserController::class, 'login'])
        ->middleware(['user.log.auth'])
        ->name('login.submit');
    
    // Registration routes
    Route::get('/register', [UserController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [UserController::class, 'register'])
        ->middleware(['user.log.auth'])
        ->name('register.submit');
});

// Authenticated user routes
Route::middleware(['auth'])->group(function () {
    // Logout
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    
    // Profile management routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [UserController::class, 'profile'])->name('show');
        Route::get('/edit', [UserController::class, 'profile'])->name('edit');
        Route::put('/', [UserController::class, 'updateProfile'])->name('update');
        
        // Password change (requires current password validation)
        Route::put('/password', [UserController::class, 'changePassword'])
            ->middleware(['user.validate.password'])
            ->name('password.change');
    });
});

// API authentication routes
Route::prefix('api/auth')->name('api.auth.')->group(function () {
    // Public auth endpoints
    Route::middleware(['guest', 'user.auth.rate.limit:5,1'])->group(function () {
        Route::post('/login', [UserController::class, 'login'])->name('login');
        Route::post('/register', [UserController::class, 'register'])->name('register');
    });
    
    // Protected auth endpoints
    Route::middleware(['auth:api'])->group(function () {
        Route::post('/logout', [UserController::class, 'logout'])->name('logout');
        Route::get('/user', [UserController::class, 'profile'])->name('user');
        Route::put('/user', [UserController::class, 'updateProfile'])->name('user.update');
        Route::put('/password', [UserController::class, 'changePassword'])->name('password.change');
    });
});