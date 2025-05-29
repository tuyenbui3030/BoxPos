<?php

use Illuminate\Support\Facades\Route;

// Route::middleware(['auth'])->group(function () {
//     Route::get('/customers', \Packages\Customer\Livewire\CustomerManagement::class)->name('customers');
// });

Route::middleware(['auth'])->group(function () {
    // Use the Livewire component directly instead of returning a view
    Route::get('/customers', \Packages\Customer\Livewire\CustomerManagement::class)->name('customers');
    
    // If you have other Livewire components, use them similarly
    Route::get('/customers/create', \Packages\Customer\Livewire\CreateCustomer::class)->name('customers.create');
    
    // For components that need parameters, you might need controller methods
    Route::get('/customers/{customer}', function ($customer) {
        return view('livewire.customer-detail', compact('customer'));
    })->name('customers.show');
    
    Route::get('/customers/{customer}/edit', function ($customer) {
        return view('livewire.edit-customer', compact('customer'));
    })->name('customers.edit');
});