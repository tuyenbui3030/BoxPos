<?php

use Illuminate\Support\Facades\Route;
use Packages\Marketplace\Http\Controllers\MarketplaceController;
use Packages\Marketplace\Http\Controllers\SubscriptionController;

/*
|--------------------------------------------------------------------------
| Marketplace Package Routes
|--------------------------------------------------------------------------
|
| Routes for app marketplace and subscription management
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Marketplace routes
    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        Route::get('/', [MarketplaceController::class, 'index'])->name('index');
        Route::get('/{app}', [MarketplaceController::class, 'show'])->name('show');
        Route::post('/{app}/subscribe', [MarketplaceController::class, 'subscribe'])->name('subscribe');
        Route::post('/{app}/trial', [MarketplaceController::class, 'startTrial'])->name('trial');
    });
    
    // Subscription management routes
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::get('/{subscription}', [SubscriptionController::class, 'show'])->name('show');
        Route::post('/{subscription}/upgrade', [SubscriptionController::class, 'upgrade'])->name('upgrade');
        Route::post('/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/{subscription}/reactivate', [SubscriptionController::class, 'reactivate'])->name('reactivate');
    });
    
    // Billing routes
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'billing'])->name('index');
    });
    
});