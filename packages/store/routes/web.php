<?php

use Illuminate\Support\Facades\Route;
use Packages\Store\Http\Controllers\StoreController;
use Packages\Store\Http\Controllers\StoreUserController;

/*
|--------------------------------------------------------------------------
| Store Web Routes
|--------------------------------------------------------------------------
|
| Here are the web routes for the Store package. These routes are loaded
| by the StoreServiceProvider within a group which contains the "web"
| middleware group.
|
*/

Route::middleware(['auth'])->group(function () {

    // Store management routes
    Route::prefix('stores')->name('stores.')->group(function () {
        Route::get('/', [StoreController::class, 'index'])->name('index');
        Route::get('/create', [StoreController::class, 'create'])->name('create');
        Route::post('/', [StoreController::class, 'store'])->name('store');
        Route::get('/{store}', [StoreController::class, 'show'])->name('show');
        Route::get('/{store}/edit', [StoreController::class, 'edit'])->name('edit');
        Route::put('/{store}', [StoreController::class, 'update'])->name('update');
        Route::delete('/{store}', [StoreController::class, 'destroy'])->name('destroy');

        // Store status management
        Route::patch('/{store}/activate', [StoreController::class, 'activate'])->name('activate');
        Route::patch('/{store}/deactivate', [StoreController::class, 'deactivate'])->name('deactivate');
        Route::patch('/{store}/suspend', [StoreController::class, 'suspend'])->name('suspend');

        // Store user management
        Route::prefix('{store}/users')->name('users.')->group(function () {
            Route::get('/', [StoreUserController::class, 'index'])->name('index');
            Route::post('/', [StoreUserController::class, 'store'])->name('store');
            Route::patch('/{user}', [StoreUserController::class, 'update'])->name('update');
            Route::delete('/{user}', [StoreUserController::class, 'destroy'])->name('destroy');
            Route::patch('/{user}/activate', [StoreUserController::class, 'activate'])->name('activate');
            Route::patch('/{user}/deactivate', [StoreUserController::class, 'deactivate'])->name('deactivate');
        });
    });

    // Store selection and switching
    Route::get('/store-selection', [StoreController::class, 'selection'])->name('store.selection');
    Route::match(['GET', 'POST'], '/store/switch', [StoreController::class, 'switch'])->name('store.switch');

});
