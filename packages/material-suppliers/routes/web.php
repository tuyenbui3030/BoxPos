<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Material Suppliers Package Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Material Suppliers package.
|
*/

Route::middleware(['web', 'auth'])->prefix('material-suppliers')->name('material-suppliers.')->group(function () {
    
    // Material Suppliers Routes
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', function() { return 'Material Suppliers Index'; })->name('index');
    });

    // Supplier Contacts Routes
    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/', function() { return 'Supplier Contacts Index'; })->name('index');
    });
});
