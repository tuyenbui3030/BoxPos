<?php

namespace Packages\Common\Repositories;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Packages\Common\Traits\Loggable;

class UserStoreRepository
{
    use Loggable;

    /**
     * Check if user has access to a store
     */
    public function hasStoreAccess(int $userId, int $storeId): bool
    {
        $this->logActivity('checking_user_store_access', [
            'user_id' => $userId,
            'store_id' => $storeId,
        ]);

        return DB::table('user_stores')
            ->where('user_id', $userId)
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get all stores accessible by a user
     */
    public function getUserStores(int $userId): Collection
    {
        $this->logActivity('fetching_user_stores', [
            'user_id' => $userId,
        ]);

        return Store::whereHas('userStores', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->where('is_active', true);
        })->get();
    }

    /**
     * Grant user access to a store
     */
    public function grantStoreAccess(int $userId, int $storeId, array $permissions = []): bool
    {
        $this->logActivity('granting_store_access', [
            'user_id' => $userId,
            'store_id' => $storeId,
            'permissions' => $permissions,
        ]);

        try {
            DB::table('user_stores')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'store_id' => $storeId,
                ],
                [
                    'permissions' => json_encode($permissions),
                    'is_active' => true,
                    'granted_at' => now(),
                    'granted_by' => auth()->id(),
                    'updated_at' => now(),
                ]
            );

            return true;
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'grant_store_access',
                'user_id' => $userId,
                'store_id' => $storeId,
            ]);

            return false;
        }
    }

    /**
     * Revoke user access to a store
     */
    public function revokeStoreAccess(int $userId, int $storeId): bool
    {
        $this->logActivity('revoking_store_access', [
            'user_id' => $userId,
            'store_id' => $storeId,
        ]);

        try {
            DB::table('user_stores')
                ->where('user_id', $userId)
                ->where('store_id', $storeId)
                ->update([
                    'is_active' => false,
                    'revoked_at' => now(),
                    'revoked_by' => auth()->id(),
                    'updated_at' => now(),
                ]);

            return true;
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'revoke_store_access',
                'user_id' => $userId,
                'store_id' => $storeId,
            ]);

            return false;
        }
    }

    /**
     * Update user permissions for a store
     */
    public function updateStorePermissions(int $userId, int $storeId, array $permissions): bool
    {
        $this->logActivity('updating_store_permissions', [
            'user_id' => $userId,
            'store_id' => $storeId,
            'permissions' => $permissions,
        ]);

        try {
            DB::table('user_stores')
                ->where('user_id', $userId)
                ->where('store_id', $storeId)
                ->where('is_active', true)
                ->update([
                    'permissions' => json_encode($permissions),
                    'updated_at' => now(),
                ]);

            return true;
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'update_store_permissions',
                'user_id' => $userId,
                'store_id' => $storeId,
            ]);

            return false;
        }
    }

    /**
     * Get user permissions for a store
     */
    public function getUserStorePermissions(int $userId, int $storeId): array
    {
        $this->logActivity('fetching_user_store_permissions', [
            'user_id' => $userId,
            'store_id' => $storeId,
        ]);

        $userStore = DB::table('user_stores')
            ->where('user_id', $userId)
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->first();

        if (!$userStore || !$userStore->permissions) {
            return [];
        }

        return json_decode($userStore->permissions, true) ?: [];
    }

    /**
     * Get count of users for a store
     */
    public function getStoreUserCount(int $storeId): int
    {
        return DB::table('user_stores')
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->count();
    }

    /**
     * Get user store relationship details
     */
    public function getUserStoreDetails(int $userId, int $storeId): ?array
    {
        $this->logActivity('fetching_user_store_details', [
            'user_id' => $userId,
            'store_id' => $storeId,
        ]);

        $userStore = DB::table('user_stores')
            ->where('user_id', $userId)
            ->where('store_id', $storeId)
            ->first();

        if (!$userStore) {
            return null;
        }

        return [
            'user_id' => $userStore->user_id,
            'store_id' => $userStore->store_id,
            'permissions' => json_decode($userStore->permissions, true) ?: [],
            'is_active' => $userStore->is_active,
            'granted_at' => $userStore->granted_at,
            'granted_by' => $userStore->granted_by,
            'revoked_at' => $userStore->revoked_at,
            'revoked_by' => $userStore->revoked_by,
            'updated_at' => $userStore->updated_at,
        ];
    }

    /**
     * Get all user-store relationships for a user
     */
    public function getAllUserStoreRelationships(int $userId): Collection
    {
        $this->logActivity('fetching_all_user_store_relationships', [
            'user_id' => $userId,
        ]);

        return collect(DB::table('user_stores')
            ->join('stores', 'user_stores.store_id', '=', 'stores.id')
            ->where('user_stores.user_id', $userId)
            ->select([
                'user_stores.*',
                'stores.name as store_name',
                'stores.code as store_code',
            ])
            ->get()
            ->map(function ($item) {
                $item->permissions = json_decode($item->permissions, true) ?: [];
                return $item;
            }));
    }

    /**
     * Bulk grant store access to multiple users
     */
    public function bulkGrantStoreAccess(array $userIds, int $storeId, array $permissions = []): bool
    {
        $this->logActivity('bulk_granting_store_access', [
            'user_ids' => $userIds,
            'store_id' => $storeId,
            'permissions' => $permissions,
            'user_count' => count($userIds),
        ]);

        try {
            $data = [];
            $now = now();
            $grantedBy = auth()->id();

            foreach ($userIds as $userId) {
                $data[] = [
                    'user_id' => $userId,
                    'store_id' => $storeId,
                    'permissions' => json_encode($permissions),
                    'is_active' => true,
                    'granted_at' => $now,
                    'granted_by' => $grantedBy,
                    'updated_at' => $now,
                ];
            }

            DB::table('user_stores')->upsert(
                $data,
                ['user_id', 'store_id'],
                ['permissions', 'is_active', 'granted_at', 'granted_by', 'updated_at']
            );

            return true;
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'bulk_grant_store_access',
                'user_ids' => $userIds,
                'store_id' => $storeId,
            ]);

            return false;
        }
    }

    /**
     * Bulk revoke store access from multiple users
     */
    public function bulkRevokeStoreAccess(array $userIds, int $storeId): bool
    {
        $this->logActivity('bulk_revoking_store_access', [
            'user_ids' => $userIds,
            'store_id' => $storeId,
            'user_count' => count($userIds),
        ]);

        try {
            DB::table('user_stores')
                ->whereIn('user_id', $userIds)
                ->where('store_id', $storeId)
                ->update([
                    'is_active' => false,
                    'revoked_at' => now(),
                    'revoked_by' => auth()->id(),
                    'updated_at' => now(),
                ]);

            return true;
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'bulk_revoke_store_access',
                'user_ids' => $userIds,
                'store_id' => $storeId,
            ]);

            return false;
        }
    }
}