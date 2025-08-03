<?php

namespace Packages\Common\Services;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Packages\Common\Contracts\StoreContextInterface;
use Packages\Common\Repositories\UserStoreRepository;
use Packages\Common\Traits\Loggable;

class StoreService implements StoreContextInterface
{
    use Loggable;

    protected UserStoreRepository $userStoreRepository;
    protected ?Store $currentStore = null;

    public function __construct(UserStoreRepository $userStoreRepository)
    {
        $this->userStoreRepository = $userStoreRepository;
    }

    /**
     * Get the current store
     */
    public function getCurrentStore(): ?Store
    {
        return $this->currentStore;
    }

    /**
     * Set the current store
     */
    public function setCurrentStore(int $storeId): void
    {
        $this->currentStore = Store::find($storeId);
        
        $this->logActivity('store_context_set', [
            'store_id' => $storeId,
            'store_name' => $this->currentStore?->name,
        ]);
    }

    /**
     * Switch to a different store
     */
    public function switchStore(int $storeId): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            $this->logSecurityEvent('store_switch_attempt_without_auth', [
                'attempted_store_id' => $storeId,
            ]);
            return false;
        }

        // Check if user has access to the target store
        if (!$this->hasStoreAccess($user, $storeId)) {
            $this->logSecurityEvent('unauthorized_store_switch_attempt', [
                'user_id' => $user->id,
                'attempted_store_id' => $storeId,
                'current_store_id' => $user->current_store_id,
            ]);
            return false;
        }

        $previousStoreId = $user->current_store_id;

        // Update user's current store
        $user->update(['current_store_id' => $storeId]);

        // Set current store in service
        $this->setCurrentStore($storeId);

        // Clear user-specific cache
        $this->clearUserStoreCache($user->id);

        $this->logActivity('store_switched', [
            'user_id' => $user->id,
            'previous_store_id' => $previousStoreId,
            'new_store_id' => $storeId,
        ]);

        return true;
    }

    /**
     * Check if user has access to a store
     */
    public function hasStoreAccess(User $user, int $storeId): bool
    {
        $cacheKey = "user_store_access_{$user->id}_{$storeId}";
        
        return Cache::remember($cacheKey, 300, function () use ($user, $storeId) {
            return $this->userStoreRepository->hasStoreAccess($user->id, $storeId);
        });
    }

    /**
     * Get all stores accessible by a user
     */
    public function getUserStores(User $user): Collection
    {
        $cacheKey = "user_stores_{$user->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($user) {
            return $this->userStoreRepository->getUserStores($user->id);
        });
    }

    /**
     * Grant user access to a store
     */
    public function grantStoreAccess(User $user, int $storeId, array $permissions = []): bool
    {
        $this->logActivity('store_access_grant_started', [
            'user_id' => $user->id,
            'store_id' => $storeId,
            'permissions' => $permissions,
        ]);

        try {
            $result = $this->userStoreRepository->grantStoreAccess($user->id, $storeId, $permissions);
            
            // Clear cache
            $this->clearUserStoreCache($user->id);
            
            $this->logActivity('store_access_granted', [
                'user_id' => $user->id,
                'store_id' => $storeId,
                'permissions' => $permissions,
            ]);

            return $result;
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'grant_store_access',
                'user_id' => $user->id,
                'store_id' => $storeId,
            ]);

            return false;
        }
    }

    /**
     * Revoke user access to a store
     */
    public function revokeStoreAccess(User $user, int $storeId): bool
    {
        $this->logActivity('store_access_revoke_started', [
            'user_id' => $user->id,
            'store_id' => $storeId,
        ]);

        try {
            $result = $this->userStoreRepository->revokeStoreAccess($user->id, $storeId);
            
            // Clear cache
            $this->clearUserStoreCache($user->id);
            
            // If this was the user's current store, clear it
            if ($user->current_store_id === $storeId) {
                $user->update(['current_store_id' => null]);
            }

            $this->logActivity('store_access_revoked', [
                'user_id' => $user->id,
                'store_id' => $storeId,
            ]);

            return $result;
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'revoke_store_access',
                'user_id' => $user->id,
                'store_id' => $storeId,
            ]);

            return false;
        }
    }

    /**
     * Update user permissions for a store
     */
    public function updateStorePermissions(User $user, int $storeId, array $permissions): bool
    {
        $this->logActivity('store_permissions_update_started', [
            'user_id' => $user->id,
            'store_id' => $storeId,
            'permissions' => $permissions,
        ]);

        try {
            $result = $this->userStoreRepository->updateStorePermissions($user->id, $storeId, $permissions);
            
            // Clear cache
            $this->clearUserStoreCache($user->id);

            $this->logActivity('store_permissions_updated', [
                'user_id' => $user->id,
                'store_id' => $storeId,
                'permissions' => $permissions,
            ]);

            return $result;
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'update_store_permissions',
                'user_id' => $user->id,
                'store_id' => $storeId,
            ]);

            return false;
        }
    }

    /**
     * Get user permissions for a store
     */
    public function getUserStorePermissions(User $user, int $storeId): array
    {
        $cacheKey = "user_store_permissions_{$user->id}_{$storeId}";
        
        return Cache::remember($cacheKey, 300, function () use ($user, $storeId) {
            return $this->userStoreRepository->getUserStorePermissions($user->id, $storeId);
        });
    }

    /**
     * Check if user has specific permission for a store
     */
    public function hasStorePermission(User $user, int $storeId, string $permission): bool
    {
        $permissions = $this->getUserStorePermissions($user, $storeId);
        return in_array($permission, $permissions);
    }

    /**
     * Clear user store cache
     */
    protected function clearUserStoreCache(int $userId): void
    {
        $patterns = [
            "user_stores_{$userId}",
            "user_store_access_{$userId}_*",
            "user_store_permissions_{$userId}_*",
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($pattern, '*')) {
                // For wildcard patterns, we'd need to implement cache tag clearing
                // For now, we'll clear specific known keys
                continue;
            }
            Cache::forget($pattern);
        }

        $this->logActivity('user_store_cache_cleared', [
            'user_id' => $userId,
        ]);
    }

    /**
     * Validate store context for current request
     */
    public function validateStoreContext(): bool
    {
        $user = auth()->user();
        
        if (!$user || !$user->current_store_id) {
            return false;
        }

        return $this->hasStoreAccess($user, $user->current_store_id);
    }

    /**
     * Get store statistics
     */
    public function getStoreStatistics(int $storeId): array
    {
        $cacheKey = "store_statistics_{$storeId}";
        
        return Cache::remember($cacheKey, 600, function () use ($storeId) {
            // This would be implemented based on specific business requirements
            return [
                'total_users' => $this->userStoreRepository->getStoreUserCount($storeId),
                'active_sessions' => 0, // Would be implemented with session tracking
                'last_activity' => now(),
            ];
        });
    }
}