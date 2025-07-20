<?php

use Illuminate\Support\Facades\Route;
use Packages\MaterialCatalog\Livewire\MaterialUnits\MaterialUnitsManagement;
use Packages\MaterialCatalog\Livewire\MaterialCategories\MaterialCategoriesManagement;
use Packages\MaterialCatalog\Livewire\BuildingMaterials\BuildingMaterialsManagement;

/*
|--------------------------------------------------------------------------
| Material Catalog Package Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Material Catalog package.
|
*/

// Localized Material Catalog Routes
Route::group([
    'prefix' => '{locale}/material-catalog',
    'where' => ['locale' => 'vi|en'],
    'middleware' => ['web', 'auth', 'tenant.context', 'tenant.isolation']
], function () {

    // Material Units Routes
    Route::prefix('units')->name('material-catalog.units.')->group(function () {
        Route::get('/', MaterialUnitsManagement::class)->name('index');
    });

    // Material Categories Routes
    Route::prefix('categories')->name('material-catalog.categories.')->group(function () {
        Route::get('/', MaterialCategoriesManagement::class)->name('index');
    });

    // Building Materials Routes
    Route::prefix('materials')->name('material-catalog.materials.')->group(function () {
        Route::get('/', BuildingMaterialsManagement::class)->name('index');
    });
});

// Fallback routes (redirect to localized versions)
Route::middleware(['web', 'auth'])->prefix('material-catalog')->group(function () {
    Route::get('/units', function() {
        return redirect('/' . app()->getLocale() . '/material-catalog/units');
    });

    Route::get('/categories', function() {
        return redirect('/' . app()->getLocale() . '/material-catalog/categories');
    });

    Route::get('/materials', function() {
        return redirect('/' . app()->getLocale() . '/material-catalog/materials');
    });
});
