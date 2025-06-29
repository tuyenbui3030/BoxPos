<?php

namespace Packages\Store\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Packages\Log\Traits\Loggable;
use Packages\Store\Events\StoreCreated;
use Packages\Store\Events\StoreUpdated;
use Packages\Store\Events\StoreDeleted;
use Packages\Store\Events\UserAddedToStore;
use Packages\Store\Events\UserRemovedFromStore;
use Packages\Store\Exceptions\StoreNotFoundException;
use Packages\Store\Exceptions\UserAlreadyInStoreException;
use Packages\Store\Exceptions\UserNotInStoreException;
use Packages\Store\Models\Store;
use Packages\Store\Models\UserStore;
use Packages\Store\Repositories\StoreRepository;

class StoreService
{
    use Loggable;

    protected StoreRepository $storeRepository;

    public function __construct(StoreRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * Create a new store.
     */
    public function createStore(array $data): Store
    {
        $this->logActivity('store_creation_started', [
            'user_id' => Auth::id(),
            'store_name' => $data['name'] ?? 'Unknown',
        ]);

        DB::beginTransaction();

        try {
            // Set default values
            $data = array_merge([
                'status' => Store::STATUS_ACTIVE,
                'timezone' => config('app.timezone', 'UTC'),
                'currency' => 'USD',
                'language' => 'en',
                'settings' => [],
            ], $data);

            $store = $this->storeRepository->create($data);

            // Add creator as admin if authenticated
            if (Auth::check()) {
                $this->addUserToStore($store, Auth::id(), UserStore::ROLE_ADMIN);
            }

            event(new StoreCreated($store));

            $this->logActivity('store_created', [
                'store_id' => $store->id,
                'store_name' => $store->name,
                'store_slug' => $store->slug,
                'user_id' => Auth::id(),
            ]);

            DB::commit();
            return $store;

        } catch (\Exception $e) {
            DB::rollback();

            $this->logError($e, [
                'action' => 'store_creation',
                'user_id' => Auth::id(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Update store.
     */
    public function updateStore(int $storeId, array $data): Store
    {
        $store = $this->getStoreById($storeId);

        $this->logActivity('store_update_started', [
            'store_id' => $store->id,
            'user_id' => Auth::id(),
            'updated_fields' => array_keys($data),
        ]);

        try {
            $originalData = $store->toArray();
            $updatedStore = $this->storeRepository->update($store, $data);

            event(new StoreUpdated($updatedStore, $originalData));

            $this->logActivity('store_updated', [
                'store_id' => $updatedStore->id,
                'store_name' => $updatedStore->name,
                'user_id' => Auth::id(),
                'changes' => array_keys($data),
            ]);

            return $updatedStore;

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'store_update',
                'store_id' => $store->id,
                'user_id' => Auth::id(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Delete store.
     */
    public function deleteStore(int $storeId): bool
    {
        $store = $this->getStoreById($storeId);

        $this->logActivity('store_deletion_started', [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'user_id' => Auth::id(),
        ]);

        DB::beginTransaction();

        try {
            $storeData = $store->toArray();
            $deleted = $this->storeRepository->delete($store);

            if ($deleted) {
                event(new StoreDeleted($storeData));

                $this->logActivity('store_deleted', [
                    'store_id' => $storeData['id'],
                    'store_name' => $storeData['name'],
                    'user_id' => Auth::id(),
                ]);
            }

            DB::commit();
            return $deleted;

        } catch (\Exception $e) {
            DB::rollback();

            $this->logError($e, [
                'action' => 'store_deletion',
                'store_id' => $store->id,
                'user_id' => Auth::id(),
            ]);

            throw $e;
        }
    }

    /**
     * Get store by ID.
     */
    public function getStoreById(int $storeId): Store
    {
        $cacheKey = "store_data_{$storeId}";

        $store = cache()->remember($cacheKey, 300, function () use ($storeId) {
            return $this->storeRepository->findById($storeId);
        });

        if (!$store) {
            throw new StoreNotFoundException("Store with ID {$storeId} not found");
        }

        return $store;
    }

    /**
     * Get store by slug.
     */
    public function getStoreBySlug(string $slug): Store
    {
        $store = $this->storeRepository->findBySlug($slug);

        if (!$store) {
            throw new StoreNotFoundException("Store with slug '{$slug}' not found");
        }

        return $store;
    }

    /**
     * Get stores for user.
     */
    public function getStoresForUser(int $userId): Collection
    {
        return $this->storeRepository->getForUser($userId);
    }

    /**
     * Search stores.
     */
    public function searchStores(array $criteria, int $perPage = 15): LengthAwarePaginator
    {
        return $this->storeRepository->search($criteria, $perPage);
    }

    /**
     * Add user to store.
     */
    public function addUserToStore(Store $store, int $userId, string $role = UserStore::ROLE_STAFF, array $permissions = []): UserStore
    {
        // Check if user is already in store
        if ($this->storeRepository->userHasAccess($store, $userId)) {
            throw new UserAlreadyInStoreException("User {$userId} is already in store {$store->id}");
        }

        $this->logActivity('user_add_to_store_started', [
            'store_id' => $store->id,
            'target_user_id' => $userId,
            'role' => $role,
            'user_id' => Auth::id(),
        ]);

        try {
            $userStore = $this->storeRepository->addUser($store, $userId, $role, $permissions);

            event(new UserAddedToStore($store, $userId, $role));

            $this->logActivity('user_added_to_store', [
                'store_id' => $store->id,
                'target_user_id' => $userId,
                'role' => $role,
                'user_id' => Auth::id(),
            ]);

            return $userStore;

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'add_user_to_store',
                'store_id' => $store->id,
                'target_user_id' => $userId,
                'user_id' => Auth::id(),
            ]);

            throw $e;
        }
    }

    /**
     * Remove user from store.
     */
    public function removeUserFromStore(Store $store, int $userId): bool
    {
        // Check if user is in store
        if (!$this->storeRepository->userHasAccess($store, $userId)) {
            throw new UserNotInStoreException("User {$userId} is not in store {$store->id}");
        }

        $this->logActivity('user_remove_from_store_started', [
            'store_id' => $store->id,
            'target_user_id' => $userId,
            'user_id' => Auth::id(),
        ]);

        try {
            $removed = $this->storeRepository->removeUser($store, $userId);

            if ($removed) {
                event(new UserRemovedFromStore($store, $userId));

                $this->logActivity('user_removed_from_store', [
                    'store_id' => $store->id,
                    'target_user_id' => $userId,
                    'user_id' => Auth::id(),
                ]);
            }

            return $removed;

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'remove_user_from_store',
                'store_id' => $store->id,
                'target_user_id' => $userId,
                'user_id' => Auth::id(),
            ]);

            throw $e;
        }
    }

    /**
     * Update user role in store.
     */
    public function updateUserRole(Store $store, int $userId, string $role, array $permissions = []): bool
    {
        $this->logActivity('user_role_update_started', [
            'store_id' => $store->id,
            'target_user_id' => $userId,
            'new_role' => $role,
            'user_id' => Auth::id(),
        ]);

        try {
            $updated = $this->storeRepository->updateUserRole($store, $userId, $role, $permissions);

            $this->logActivity('user_role_updated', [
                'store_id' => $store->id,
                'target_user_id' => $userId,
                'new_role' => $role,
                'user_id' => Auth::id(),
            ]);

            return $updated;

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'update_user_role',
                'store_id' => $store->id,
                'target_user_id' => $userId,
                'user_id' => Auth::id(),
            ]);

            throw $e;
        }
    }

    /**
     * Check if user has permission in store.
     */
    public function userHasPermission(Store $store, int $userId, string $permission): bool
    {
        return $this->storeRepository->userHasPermission($store, $userId, $permission);
    }

    /**
     * Get store statistics.
     */
    public function getStoreStatistics(): array
    {
        return $this->storeRepository->countByStatus();
    }

    /**
     * Activate store.
     */
    public function activateStore(int $storeId): Store
    {
        return $this->updateStore($storeId, ['status' => Store::STATUS_ACTIVE]);
    }

    /**
     * Deactivate store.
     */
    public function deactivateStore(int $storeId): Store
    {
        return $this->updateStore($storeId, ['status' => Store::STATUS_INACTIVE]);
    }

    /**
     * Suspend store.
     */
    public function suspendStore(int $storeId): Store
    {
        return $this->updateStore($storeId, ['status' => Store::STATUS_SUSPENDED]);
    }

    /**
     * Get store repository instance.
     */
    public function getStoreRepository(): StoreRepository
    {
        return $this->storeRepository;
    }
}
