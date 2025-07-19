<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Material Catalog Package Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Material Catalog package.
|
*/

Route::middleware(['web', 'auth'])->prefix('material-catalog')->name('material-catalog.')->group(function () {
    
    // Material Units Routes
    Route::prefix('units')->name('units.')->group(function () {
        Route::get('/', function() { return 'Material Units Index'; })->name('index');
    });

    // Material Categories Routes
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', function() { return 'Material Categories Index'; })->name('index');
    });

    // Building Materials Routes
    Route::prefix('materials')->name('materials.')->group(function () {
        Route::get('/', function() { return 'Building Materials Index'; })->name('index');
    });
});
