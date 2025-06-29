<?php

namespace Packages\Store\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Packages\Store\Models\Store;
use Packages\Store\Models\UserStore;

class StoreRepository
{
    protected Store $model;

    public function __construct(Store $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new store.
     */
    public function create(array $data): Store
    {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Store::generateSlug($data['name']);
        }

        return $this->model->create($data);
    }

    /**
     * Find store by ID.
     */
    public function findById(int $id): ?Store
    {
        return $this->model->find($id);
    }

    /**
     * Find store by slug.
     */
    public function findBySlug(string $slug): ?Store
    {
        return $this->model->query()->bySlug($slug)->first();
    }

    /**
     * Find store by domain.
     */
    public function findByDomain(string $domain): ?Store
    {
        return $this->model->query()->byDomain($domain)->first();
    }

    /**
     * Update store.
     */
    public function update(Store $store, array $data): Store
    {
        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $store->name && empty($data['slug'])) {
            $data['slug'] = Store::generateSlug($data['name']);
        }

        $store->update($data);
        return $store->fresh();
    }

    /**
     * Delete store.
     */
    public function delete(Store $store): bool
    {
        return $store->delete();
    }

    /**
     * Get all stores.
     */
    public function getAll(): Collection
    {
        return $this->model->query()->orderByName()->get();
    }

    /**
     * Get active stores.
     */
    public function getActive(): Collection
    {
        return $this->model->query()->active()->orderByName()->get();
    }

    /**
     * Get stores for user.
     */
    public function getForUser(int $userId): Collection
    {
        return $this->model->query()
            ->forUser($userId)
            ->active()
            ->withUserPivot()
            ->orderByName()
            ->get();
    }

    /**
     * Get stores where user is admin.
     */
    public function getForUserAsAdmin(int $userId): Collection
    {
        return $this->model->query()
            ->forUserAsAdmin($userId)
            ->active()
            ->withUserPivot()
            ->orderByName()
            ->get();
    }

    /**
     * Search stores with pagination.
     */
    public function search(array $criteria, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()
            ->applyFilters($criteria)
            ->withUserCount()
            ->orderByName()
            ->paginate($perPage);
    }

    /**
     * Get stores for scenario.
     */
    public function getForScenario(string $scenario): Collection
    {
        return $this->model->query()
            ->forScenario($scenario)
            ->get();
    }

    /**
     * Add user to store.
     */
    public function addUser(Store $store, int $userId, string $role = 'staff', array $permissions = []): UserStore
    {
        // Get default permissions for role if not provided
        if (empty($permissions)) {
            $permissions = UserStore::getDefaultPermissions($role);
        }

        return $store->users()->attach($userId, [
            'role' => $role,
            'permissions' => $permissions,
            'is_active' => true,
            'joined_at' => now(),
        ]);
    }

    /**
     * Remove user from store.
     */
    public function removeUser(Store $store, int $userId): bool
    {
        return $store->users()->detach($userId) > 0;
    }

    /**
     * Update user role in store.
     */
    public function updateUserRole(Store $store, int $userId, string $role, array $permissions = []): bool
    {
        // Get default permissions for role if not provided
        if (empty($permissions)) {
            $permissions = UserStore::getDefaultPermissions($role);
        }

        return $store->users()->updateExistingPivot($userId, [
            'role' => $role,
            'permissions' => $permissions,
        ]) > 0;
    }

    /**
     * Activate user in store.
     */
    public function activateUser(Store $store, int $userId): bool
    {
        return $store->users()->updateExistingPivot($userId, [
            'is_active' => true,
        ]) > 0;
    }

    /**
     * Deactivate user in store.
     */
    public function deactivateUser(Store $store, int $userId): bool
    {
        return $store->users()->updateExistingPivot($userId, [
            'is_active' => false,
        ]) > 0;
    }

    /**
     * Get user's role in store.
     */
    public function getUserRole(Store $store, int $userId): ?string
    {
        $userStore = $store->users()->wherePivot('user_id', $userId)->first();
        return $userStore?->pivot->role;
    }

    /**
     * Check if user has access to store.
     */
    public function userHasAccess(Store $store, int $userId): bool
    {
        return $store->users()
            ->wherePivot('user_id', $userId)
            ->wherePivot('is_active', true)
            ->exists();
    }

    /**
     * Check if user has permission in store.
     */
    public function userHasPermission(Store $store, int $userId, string $permission): bool
    {
        $userStore = $store->users()
            ->wherePivot('user_id', $userId)
            ->wherePivot('is_active', true)
            ->first();

        if (!$userStore) {
            return false;
        }

        $pivot = $userStore->pivot;

        // Admin has all permissions
        if ($pivot->role === 'admin') {
            return true;
        }

        $permissions = $pivot->permissions ?? [];

        // Handle JSON string permissions
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?? [];
        }

        return in_array($permission, $permissions);
    }

    /**
     * Get stores with statistics.
     */
    public function getWithStatistics(): Collection
    {
        return $this->model->query()
            ->withUserCount()
            ->orderByName()
            ->get();
    }

    /**
     * Get recent stores.
     */
    public function getRecent(int $limit = 10): Collection
    {
        return $this->model->query()
            ->active()
            ->orderByCreated()
            ->limit($limit)
            ->get();
    }

    /**
     * Get stores by status.
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->query()
            ->byStatus($status)
            ->orderByName()
            ->get();
    }

    /**
     * Count stores by status.
     */
    public function countByStatus(): array
    {
        return [
            'active' => $this->model->query()->active()->count(),
            'inactive' => $this->model->query()->inactive()->count(),
            'suspended' => $this->model->query()->suspended()->count(),
            'total' => $this->model->query()->count(),
        ];
    }

    /**
     * Get stores created in date range.
     */
    public function getCreatedBetween($startDate, $endDate): Collection
    {
        return $this->model->query()
            ->createdBetween($startDate, $endDate)
            ->orderByCreated()
            ->get();
    }
}
