<?php

use Packages\User\Livewire\Dashboard;
use Packages\User\Livewire\ManageDevices;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/devices', ManageDevices::class)->name('devices');
    Route::get('/customers', \Packages\Customer\Livewire\CustomerManagement::class)->name('customers');
    
    // Add more authenticated routes here
});

// Temporary password reset routes (should be moved to User package later)
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request')->middleware('guest');

Route::post('/forgot-password', function () {
    return back()->with('status', 'Password reset link sent!');
})->name('password.email')->middleware('guest');
