<?php

namespace Packages\Common\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Packages\Common\Helpers\StoreContext;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Store permission directive
        Blade::if('storePermission', function (string $permission) {
            return StoreContext::hasPermission($permission);
        });

        // Store admin directive
        Blade::if('storeAdmin', function () {
            return StoreContext::isStoreAdmin();
        });

        // Current store directive
        Blade::directive('currentStore', function () {
            return "<?php echo \Packages\Common\Helpers\StoreContext::getCurrentStore()?->name ?? 'No Store Selected'; ?>";
        });

        // Store ID directive
        Blade::directive('currentStoreId', function () {
            return "<?php echo \Packages\Common\Helpers\StoreContext::getCurrentStoreId() ?? 0; ?>";
        });

        // Store context validation directive
        Blade::if('validStoreContext', function () {
            return StoreContext::validate();
        });

        // User stores directive
        Blade::directive('userStores', function () {
            return "<?php echo json_encode(\Packages\Common\Helpers\StoreContext::getUserStores()->toArray()); ?>";
        });

        // Store permissions directive
        Blade::directive('storePermissions', function () {
            return "<?php echo json_encode(\Packages\Common\Helpers\StoreContext::getPermissions()); ?>";
        });

        // Specific permission checks
        Blade::if('canManageUsers', function () {
            return StoreContext::canManageUsers();
        });

        Blade::if('canManageInventory', function () {
            return StoreContext::canManageInventory();
        });

        Blade::if('canProcessSales', function () {
            return StoreContext::canProcessSales();
        });

        Blade::if('canViewReports', function () {
            return StoreContext::canViewReports();
        });

        Blade::if('canManageFinances', function () {
            return StoreContext::canManageFinances();
        });
    }
}