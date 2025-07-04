<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register main application routes. Package-specific
| routes are handled by their respective service providers.
|
*/

// Note: Language switching routes are now handled by Localization package

// Note: Language switching is handled by:
// - Livewire LanguageSwitcher component in navigation
// - /language/{locale} route for programmatic switching
// - LocalizationMiddleware for URL-based locale detection







// Localized routes group
Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => 'vi|en'],
    'middleware' => ['web']
], function () {

    // Livewire routes for localized URLs (update route handled globally)
    Route::post('/livewire/upload-file', '\Livewire\Features\SupportFileUploads\FileUploadController@handle');
    Route::get('/livewire/preview-file/{filename}', '\Livewire\Features\SupportFileUploads\FilePreviewController@handle');





    // Home route redirects to dashboard
    Route::get('/', function ($locale) {
        if (Auth::check()) {
            return redirect("/$locale/dashboard");
        }
        return redirect("/$locale/login");
    })->name('locale.home');

    // Guest routes
    Route::middleware(['guest'])->group(function () {
        Route::get('/login', \Packages\User\Livewire\Login::class)->name('locale.login');
        Route::get('/register', \Packages\User\Livewire\Register::class)->name('locale.register');

        Route::get('/forgot-password', function () {
            return view('user::auth.forgot-password');
        })->name('locale.password.request');
    });







    // Authenticated routes
    Route::middleware(['auth', 'tenant.context', 'tenant.isolation'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('locale.dashboard');
        Route::get('/dashboard/business', [\App\Http\Controllers\DashboardController::class, 'business'])->name('locale.dashboard.business');
        Route::get('/dashboard/coffee', [\App\Http\Controllers\DashboardController::class, 'coffee'])->name('locale.dashboard.coffee');



        Route::get('/devices', \Packages\User\Livewire\ManageDevices::class)->name('locale.devices');
        Route::get('/customers', \Packages\Customer\Livewire\CustomerManagement::class)->name('locale.customers');

        // Language test page
        Route::get('/language-test', function () {
            return view('language-test');
        })->name('locale.language.test');

        // Project switching route (hybrid: config + database)
        Route::get('/store-switch', function(\Illuminate\Http\Request $request) {
            $projectId = $request->query('store_id');

            if (!$projectId) {
                session()->flash('error', 'Project ID is required');
                return back();
            }

            try {
                $projectService = app(\App\Services\ProjectService::class);

                // Switch project (config for metadata, database for access)
                $project = $projectService->switchProject((int) $projectId);

                // Clear user-specific cache
                $userId = auth()->id();
                cache()->forget("user_project_access_{$userId}_{$projectId}");
                cache()->forget("user_accessible_projects_{$userId}");
                cache()->forget("user_project_role_{$userId}_{$projectId}");

                session()->flash('success', "Switched to {$project['name']} {$project['icon']}");
                return redirect()->route('locale.dashboard', ['locale' => app()->getLocale()]);

            } catch (\Exception $e) {
                session()->flash('error', 'Failed to switch project: ' . $e->getMessage());
                return back();
            }
        })->name('locale.store.switch');



        // Logout
        Route::post('/logout', function () {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect()->route('locale.login', ['locale' => app()->getLocale()]);
        })->name('locale.logout');

        // Profile routes
        Route::prefix('profile')->name('locale.profile.')->group(function () {
            Route::get('/', [\Packages\User\Http\Controllers\UserController::class, 'profile'])->name('show');
            Route::get('/edit', [\Packages\User\Http\Controllers\UserController::class, 'profile'])->name('edit');
            Route::put('/', [\Packages\User\Http\Controllers\UserController::class, 'updateProfile'])->name('update');
            Route::put('/password', [\Packages\User\Http\Controllers\UserController::class, 'changePassword'])->name('password.change');
        });
    });
});

// Store demo routes - Easy access for local development
Route::get('/demo', function () {
    $stores = \Packages\Store\Models\Store::all();
    return view('demo.store-selector', compact('stores'));
})->name('demo.stores');

Route::get('/demo/vat-lieu', function () {
    $store = \Packages\Store\Models\Store::where('slug', 'vat-lieu-xay-dung')->first();
    if (!$store) abort(404, 'Store not found');

    // Switch to this store context
    session(['current_store_id' => $store->id]);

    $categories = \DB::table('product_categories')->where('store_id', $store->id)->get();
    $products = \DB::table('products')->where('store_id', $store->id)->get();

    return view('demo.materials', compact('store', 'categories', 'products'));
})->name('demo.materials');

Route::get('/demo/ca-phe', function () {
    $store = \Packages\Store\Models\Store::where('slug', 'kho-ca-phe')->first();
    if (!$store) abort(404, 'Store not found');

    // Switch to this store context
    session(['current_store_id' => $store->id]);

    $categories = \DB::table('product_categories')->where('store_id', $store->id)->get();
    $products = \DB::table('products')->where('store_id', $store->id)->get();

    return view('demo.coffee', compact('store', 'categories', 'products'));
})->name('demo.coffee');

// Multi-tenant demo route
Route::get('/multi-tenant-demo', function () {
    if (!Auth::check()) {
        return redirect()->route('locale.login', ['locale' => 'en']);
    }

    $tenantService = app(\Packages\Tenant\Services\TenantService::class);
    $user = Auth::user();

    // Get user stores with proper pivot data
    $userStores = $user->stores()->withPivot(['role', 'permissions', 'is_active', 'joined_at'])->get();

    $data = [
        'user' => $user,
        'currentStore' => $tenantService->getCurrentStore(),
        'userStores' => $userStores,
        'userRole' => $tenantService->getUserRole(),
        'permissions' => [
            'view_dashboard' => $tenantService->userHasPermission('view_dashboard'),
            'manage_customers' => $tenantService->userHasPermission('manage_customers'),
            'manage_products' => $tenantService->userHasPermission('manage_products'),
            'manage_settings' => $tenantService->userHasPermission('manage_settings'),
            'manage_users' => $tenantService->userHasPermission('manage_users'),
        ],
        'customers' => \Packages\Customer\Models\Customer::all(),
    ];

    return view('multi-tenant-demo', $data);
})->middleware(['auth', 'tenant.context', 'tenant.isolation'])->name('multi-tenant-demo');

// Note: Package routes will still be available without locale prefix
// Fallback routes will redirect them to localized versions
