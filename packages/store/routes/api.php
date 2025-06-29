<?php

use Illuminate\Support\Facades\Route;
use Packages\Store\Http\Controllers\Api\StoreApiController;
use Packages\Store\Http\Controllers\Api\StoreUserApiController;

/*
|--------------------------------------------------------------------------
| Store API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for the Store package. These routes are loaded
| by the StoreServiceProvider within a group which contains the "api"
| middleware group.
|
*/

Route::middleware(['auth:sanctum'])->prefix('api/v1')->group(function () {
    
    // Store API routes
    Route::prefix('stores')->name('api.stores.')->group(function () {
        Route::get('/', [StoreApiController::class, 'index'])->name('index');
        Route::post('/', [StoreApiController::class, 'store'])->name('store');
        Route::get('/{store}', [StoreApiController::class, 'show'])->name('show');
        Route::put('/{store}', [StoreApiController::class, 'update'])->name('update');
        Route::delete('/{store}', [StoreApiController::class, 'destroy'])->name('destroy');
        
        // Store status management
        Route::patch('/{store}/activate', [StoreApiController::class, 'activate'])->name('activate');
        Route::patch('/{store}/deactivate', [StoreApiController::class, 'deactivate'])->name('deactivate');
        Route::patch('/{store}/suspend', [StoreApiController::class, 'suspend'])->name('suspend');
        
        // Store statistics
        Route::get('/statistics/overview', [StoreApiController::class, 'statistics'])->name('statistics');
        
        // Store user management API
        Route::prefix('{store}/users')->name('users.')->group(function () {
            Route::get('/', [StoreUserApiController::class, 'index'])->name('index');
            Route::post('/', [StoreUserApiController::class, 'store'])->name('store');
            Route::get('/{user}', [StoreUserApiController::class, 'show'])->name('show');
            Route::put('/{user}', [StoreUserApiController::class, 'update'])->name('update');
            Route::delete('/{user}', [StoreUserApiController::class, 'destroy'])->name('destroy');
            Route::patch('/{user}/activate', [StoreUserApiController::class, 'activate'])->name('activate');
            Route::patch('/{user}/deactivate', [StoreUserApiController::class, 'deactivate'])->name('deactivate');
        });
    });
    
    // User's stores
    Route::get('/my-stores', [StoreApiController::class, 'myStores'])->name('api.my-stores');
    Route::post('/switch-store/{store}', [StoreApiController::class, 'switchStore'])->name('api.switch-store');
    
});
