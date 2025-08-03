<?php

namespace Packages\Common\Helpers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Packages\Common\Services\StoreService;

class StoreContext
{
    /**
     * Get the current store ID
     */
    public static function getCurrentStoreId(): ?int
    {
        if (!Auth::check()) {
            return null;
        }

        return Auth::user()->current_store_id;
    }

    /**
     * Get the current store
     */
    public static function getCurrentStore(): ?Store
    {
        $storeService = app(StoreService::class);
        return $storeService->getCurrentStore();
    }

    /**
     * Check if user has access to current store
     */
    public static function hasCurrentStoreAccess(): bool
    {
        if (!Auth::check() || !self::getCurrentStoreId()) {
            return false;
        }

        $storeService = app(StoreService::class);
        return $storeService->hasStoreAccess(Auth::user(), self::getCurrentStoreId());
    }

    /**
     * Get all stores accessible by current user
     */
    public static function getUserStores(): \Illuminate\Database\Eloquent\Collection
    {
        if (!Auth::check()) {
            return collect();
        }

        $storeService = app(StoreService::class);
        return $storeService->getUserStores(Auth::user());
    }

    /**
     * Switch to a different store
     */
    public static function switchStore(int $storeId): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $storeService = app(StoreService::class);
        return $storeService->switchStore($storeId);
    }

    /**
     * Check if user has specific permission for current store
     */
    public static function hasPermission(string $permission): bool
    {
        if (!Auth::check() || !self::getCurrentStoreId()) {
            return false;
        }

        $storeService = app(StoreService::class);
        return $storeService->hasStorePermission(
            Auth::user(),
            self::getCurrentStoreId(),
            $permission
        );
    }

    /**
     * Get user permissions for current store
     */
    public static function getPermissions(): array
    {
        if (!Auth::check() || !self::getCurrentStoreId()) {
            return [];
        }

        $storeService = app(StoreService::class);
        return $storeService->getUserStorePermissions(
            Auth::user(),
            self::getCurrentStoreId()
        );
    }

    /**
     * Validate store context for current request
     */
    public static function validate(): bool
    {
        $storeService = app(StoreService::class);
        return $storeService->validateStoreContext();
    }

    /**
     * Get store statistics for current store
     */
    public static function getStoreStatistics(): array
    {
        if (!self::getCurrentStoreId()) {
            return [];
        }

        $storeService = app(StoreService::class);
        return $storeService->getStoreStatistics(self::getCurrentStoreId());
    }

    /**
     * Check if current user is store admin
     */
    public static function isStoreAdmin(): bool
    {
        return self::hasPermission('admin') || self::hasPermission('store_admin');
    }

    /**
     * Check if current user can manage users
     */
    public static function canManageUsers(): bool
    {
        return self::hasPermission('manage_users') || self::isStoreAdmin();
    }

    /**
     * Check if current user can manage inventory
     */
    public static function canManageInventory(): bool
    {
        return self::hasPermission('manage_inventory') || self::isStoreAdmin();
    }

    /**
     * Check if current user can process sales
     */
    public static function canProcessSales(): bool
    {
        return self::hasPermission('process_sales') || self::isStoreAdmin();
    }

    /**
     * Check if current user can view reports
     */
    public static function canViewReports(): bool
    {
        return self::hasPermission('view_reports') || self::isStoreAdmin();
    }

    /**
     * Check if current user can manage finances
     */
    public static function canManageFinances(): bool
    {
        return self::hasPermission('manage_finances') || self::isStoreAdmin();
    }

    /**
     * Get store context for logging
     */
    public static function getLoggingContext(): array
    {
        return [
            'store_id' => self::getCurrentStoreId(),
            'store_name' => self::getCurrentStore()?->name,
            'user_id' => Auth::id(),
            'user_permissions' => self::getPermissions(),
        ];
    }
}