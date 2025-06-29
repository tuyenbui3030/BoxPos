<?php

namespace Packages\Tenant\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Packages\Log\Traits\Loggable;
use Packages\Store\Models\Store;
use Packages\Store\Services\StoreService;
use Packages\Tenant\Exceptions\NoCurrentStoreException;
use Packages\Tenant\Exceptions\StoreAccessDeniedException;

class TenantService
{
    use Loggable;

    protected StoreService $storeService;
    protected ?Store $currentStore = null;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    /**
     * Get the current store for the authenticated user.
     */
    public function getCurrentStore(): ?Store
    {
        if ($this->currentStore) {
            return $this->currentStore;
        }

        if (!Auth::check()) {
            return null;
        }

        $user = Auth::user();

        // Try to get from user's current_store_id
        if ($user->current_store_id) {
            $store = $this->storeService->getStoreById($user->current_store_id);

            // Verify user has access to this store
            if ($this->userHasAccessToStore($user->id, $store)) {
                $this->currentStore = $store;
                return $this->currentStore;
            }
        }

        // Try to get from session
        $storeId = Session::get('current_store_id');
        if ($storeId) {
            try {
                $store = $this->storeService->getStoreById($storeId);

                if ($this->userHasAccessToStore($user->id, $store)) {
                    $this->setCurrentStore($store);
                    return $this->currentStore;
                }
            } catch (\Exception $e) {
                // Store not found or access denied, clear session
                Session::forget('current_store_id');
            }
        }

        // Get user's first accessible store
        $userStores = $this->storeService->getStoresForUser($user->id);
        if ($userStores->isNotEmpty()) {
            $firstStore = $userStores->first();
            $this->setCurrentStore($firstStore);
            return $this->currentStore;
        }

        return null;
    }

    /**
     * Set the current store for the authenticated user.
     */
    public function setCurrentStore(Store $store): void
    {
        if (!Auth::check()) {
            throw new \Exception('User must be authenticated to set current store');
        }

        $user = Auth::user();

        // Verify user has access to this store
        if (!$this->userHasAccessToStore($user->id, $store)) {
            throw new StoreAccessDeniedException("User {$user->id} does not have access to store {$store->id}");
        }

        $this->logActivity('store_switched', [
            'user_id' => $user->id,
            'previous_store_id' => $this->currentStore?->id,
            'new_store_id' => $store->id,
            'store_name' => $store->name,
        ]);

        // Update user's current store
        $user->update(['current_store_id' => $store->id]);

        // Update session
        Session::put('current_store_id', $store->id);

        // Update cached current store
        $this->currentStore = $store;

        // Clear any cached data that might be store-specific
        $this->clearStoreCache();
    }

    /**
     * Get current store ID.
     */
    public function getCurrentStoreId(): ?int
    {
        $store = $this->getCurrentStore();
        return $store?->id;
    }

    /**
     * Check if user has access to a specific store.
     */
    public function userHasAccessToStore(int $userId, $store): bool
    {
        // If store is just an ID, convert to Store model
        if (is_numeric($store)) {
            $storeId = $store;

            // Cache access check for 5 minutes
            $cacheKey = "user_store_access_{$userId}_{$storeId}";

            return cache()->remember($cacheKey, 300, function () use ($userId, $storeId) {
                return DB::table('user_stores')
                    ->where('user_id', $userId)
                    ->where('store_id', $storeId)
                    ->where('is_active', true)
                    ->exists();
            });
        }

        // If store is a Store model
        return $this->storeService->getStoreRepository()->userHasAccess($store, $userId);
    }

    /**
     * Check if user has permission in current store.
     */
    public function userHasPermission(string $permission, ?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        $store = $this->getCurrentStore();

        if (!$userId || !$store) {
            return false;
        }

        return $this->storeService->userHasPermission($store, $userId, $permission);
    }

    /**
     * Get user's role in current store.
     */
    public function getUserRole(?int $userId = null): ?string
    {
        $userId = $userId ?? Auth::id();
        $store = $this->getCurrentStore();

        if (!$userId || !$store) {
            return null;
        }

        return $this->storeService->getStoreRepository()->getUserRole($store, $userId);
    }

    /**
     * Check if current user is admin in current store.
     */
    public function isAdmin(?int $userId = null): bool
    {
        return $this->getUserRole($userId) === 'admin';
    }

    /**
     * Check if current user is manager in current store.
     */
    public function isManager(?int $userId = null): bool
    {
        return in_array($this->getUserRole($userId), ['admin', 'manager']);
    }

    /**
     * Check if current user is staff in current store.
     */
    public function isStaff(?int $userId = null): bool
    {
        return in_array($this->getUserRole($userId), ['admin', 'manager', 'staff']);
    }

    /**
     * Require current store or throw exception.
     */
    public function requireCurrentStore(): Store
    {
        $store = $this->getCurrentStore();

        if (!$store) {
            throw new NoCurrentStoreException('No current store is set for the user');
        }

        return $store;
    }

    /**
     * Require permission or throw exception.
     */
    public function requirePermission(string $permission, ?int $userId = null): void
    {
        if (!$this->userHasPermission($permission, $userId)) {
            throw new StoreAccessDeniedException("User does not have permission: {$permission}");
        }
    }

    /**
     * Get all stores accessible by current user.
     */
    public function getUserStores(?int $userId = null): \Illuminate\Database\Eloquent\Collection
    {
        $userId = $userId ?? Auth::id();

        if (!$userId) {
            return collect();
        }

        return $this->storeService->getStoresForUser($userId);
    }

    /**
     * Switch to a different store.
     */
    public function switchStore(int $storeId): Store
    {
        $store = $this->storeService->getStoreById($storeId);
        $this->setCurrentStore($store);
        return $store;
    }

    /**
     * Clear store-specific cache.
     */
    protected function clearStoreCache(): void
    {
        // Clear any store-specific cached data
        // This can be extended based on your caching strategy

        // Clear cache - avoid using tags as they're not supported by all cache drivers
        if (function_exists('cache') && Auth::check()) {
            cache()->forget('current_store_' . Auth::id());
            cache()->forget('user_stores_' . Auth::id());
            cache()->forget('store_permissions_' . Auth::id());
            cache()->forget('user_role_' . Auth::id());
        }
    }

    /**
     * Get store context for logging and debugging.
     */
    public function getStoreContext(): array
    {
        $store = $this->getCurrentStore();
        $user = Auth::user();

        return [
            'store_id' => $store?->id,
            'store_name' => $store?->name,
            'store_slug' => $store?->slug,
            'user_id' => $user?->id,
            'user_role' => $this->getUserRole(),
            'session_store_id' => Session::get('current_store_id'),
        ];
    }

    /**
     * Initialize tenant context for the current request.
     */
    public function initializeTenantContext(): void
    {
        $store = $this->getCurrentStore();

        if ($store) {
            // Set application locale based on store language
            if ($store->language) {
                app()->setLocale($store->language);
            }

            // Set timezone
            if ($store->timezone) {
                config(['app.timezone' => $store->timezone]);
                date_default_timezone_set($store->timezone);
            }

            // Set currency context
            if ($store->currency) {
                config(['app.currency' => $store->currency]);
            }

            $this->logInfo('tenant_context_initialized', $this->getStoreContext());
        }
    }

    /**
     * Check if tenant context is properly set.
     */
    public function hasTenantContext(): bool
    {
        return $this->getCurrentStore() !== null;
    }

    /**
     * Reset tenant context.
     */
    public function resetTenantContext(): void
    {
        $this->currentStore = null;
        Session::forget('current_store_id');

        if (Auth::check()) {
            Auth::user()->update(['current_store_id' => null]);
        }

        $this->logActivity('tenant_context_reset', [
            'user_id' => Auth::id(),
        ]);
    }
}
