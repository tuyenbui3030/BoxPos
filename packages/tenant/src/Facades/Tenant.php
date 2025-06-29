<?php

namespace Packages\Tenant\Facades;

use Illuminate\Support\Facades\Facade;
use Packages\Store\Models\Store;

/**
 * @method static Store|null getCurrentStore()
 * @method static void setCurrentStore(Store $store)
 * @method static int|null getCurrentStoreId()
 * @method static bool userHasAccessToStore(int $userId, Store $store)
 * @method static bool userHasPermission(string $permission, int|null $userId = null)
 * @method static string|null getUserRole(int|null $userId = null)
 * @method static bool isAdmin(int|null $userId = null)
 * @method static bool isManager(int|null $userId = null)
 * @method static bool isStaff(int|null $userId = null)
 * @method static Store requireCurrentStore()
 * @method static void requirePermission(string $permission, int|null $userId = null)
 * @method static \Illuminate\Database\Eloquent\Collection getUserStores(int|null $userId = null)
 * @method static Store switchStore(int $storeId)
 * @method static array getStoreContext()
 * @method static void initializeTenantContext()
 * @method static bool hasTenantContext()
 * @method static void resetTenantContext()
 */
class Tenant extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Packages\Tenant\Services\TenantService::class;
    }
}
