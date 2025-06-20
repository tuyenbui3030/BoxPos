<?php

use Illuminate\Support\Facades\Route;
use Packages\Customer\Http\Controllers\CustomerController;
use Packages\Customer\Livewire\CustomerManagement;

/*
|--------------------------------------------------------------------------
| Customer Package Routes
|--------------------------------------------------------------------------
|
| All customer-related routes including management and CRUD operations.
|
*/

// Main customer management route (Livewire component)
Route::middleware(['auth'])->group(function () {
    Route::get('/customers', CustomerManagement::class)->name('customers');
});

// RESTful customer routes
Route::middleware(['auth'])->prefix('customers')->name('customers.')->group(function () {
    Route::get('/list', [CustomerController::class, 'index'])->name('index');
    Route::get('/create', [CustomerController::class, 'create'])->name('create');
    Route::post('/', [CustomerController::class, 'store'])->name('store');
    Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
    Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
    Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
    Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
    
    // Builder Pattern endpoints
    Route::get('/vip', [CustomerController::class, 'vipCustomers'])->name('vip');
    Route::get('/at-risk', [CustomerController::class, 'atRiskCustomers'])->name('at-risk');
    Route::get('/birthdays', [CustomerController::class, 'upcomingBirthdays'])->name('birthdays');
    Route::get('/top', [CustomerController::class, 'topCustomers'])->name('top');
    Route::get('/group/{group}', [CustomerController::class, 'customersByGroup'])->name('by-group');
});

// API routes for Customer package
Route::middleware(['auth:sanctum'])->prefix('api/customers')->name('api.customers.')->group(function () {
    Route::get('/', [CustomerController::class, 'apiIndex'])->name('index');
    Route::post('/', [CustomerController::class, 'apiStore'])->name('store');
    Route::get('/{customer}', [CustomerController::class, 'apiShow'])->name('show');
    Route::put('/{customer}', [CustomerController::class, 'apiUpdate'])->name('update');
    Route::delete('/{customer}', [CustomerController::class, 'apiDestroy'])->name('destroy');
    
    // Builder Pattern API endpoints
    Route::get('/vip', [CustomerController::class, 'vipCustomers'])->name('vip');
    Route::get('/at-risk', [CustomerController::class, 'atRiskCustomers'])->name('at-risk');
    Route::get('/birthdays', [CustomerController::class, 'upcomingBirthdays'])->name('birthdays');
    Route::get('/top', [CustomerController::class, 'topCustomers'])->name('top');
    Route::get('/group/{group}', [CustomerController::class, 'customersByGroup'])->name('by-group');
});