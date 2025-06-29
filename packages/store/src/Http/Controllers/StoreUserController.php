<?php

namespace Packages\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Packages\Store\Services\StoreService;
use Packages\Log\Traits\Loggable;

class StoreUserController extends Controller
{
    use Loggable;

    protected StoreService $storeService;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    /**
     * Display store users.
     */
    public function index(Store $store)
    {
        $this->logActivity('store_users_viewed', [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'user_id' => auth()->id(),
        ]);

        $users = $store->users()->withPivot(['role', 'permissions', 'is_active', 'joined_at'])->get();

        return response()->json([
            'success' => true,
            'data' => [
                'store' => $store,
                'users' => $users,
            ]
        ]);
    }

    /**
     * Add user to store.
     */
    public function store(Request $request, Store $store): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,manager,staff,viewer',
            'permissions' => 'array',
        ]);

        try {
            $this->storeService->addUserToStore(
                $store,
                $request->user_id,
                $request->role,
                $request->permissions ?? []
            );

            $this->logActivity('user_added_to_store', [
                'store_id' => $store->id,
                'target_user_id' => $request->user_id,
                'role' => $request->role,
                'added_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User added to store successfully.'
            ]);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'add_user_to_store',
                'store_id' => $store->id,
                'user_id' => $request->user_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add user to store: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user role/permissions in store.
     */
    public function update(Request $request, Store $store, User $user): JsonResponse
    {
        $request->validate([
            'role' => 'sometimes|in:admin,manager,staff,viewer',
            'permissions' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            $updateData = [];
            
            if ($request->has('role')) {
                $updateData['role'] = $request->role;
            }
            
            if ($request->has('permissions')) {
                $updateData['permissions'] = json_encode($request->permissions);
            }
            
            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->is_active;
            }

            $store->users()->updateExistingPivot($user->id, $updateData);

            $this->logActivity('store_user_updated', [
                'store_id' => $store->id,
                'target_user_id' => $user->id,
                'updates' => $updateData,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.'
            ]);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'update_store_user',
                'store_id' => $store->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove user from store.
     */
    public function destroy(Store $store, User $user): JsonResponse
    {
        try {
            $store->users()->detach($user->id);

            $this->logActivity('user_removed_from_store', [
                'store_id' => $store->id,
                'target_user_id' => $user->id,
                'removed_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User removed from store successfully.'
            ]);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'remove_user_from_store',
                'store_id' => $store->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove user from store: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate user in store.
     */
    public function activate(Store $store, User $user): JsonResponse
    {
        try {
            $store->users()->updateExistingPivot($user->id, ['is_active' => true]);

            $this->logActivity('store_user_activated', [
                'store_id' => $store->id,
                'target_user_id' => $user->id,
                'activated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User activated successfully.'
            ]);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'activate_store_user',
                'store_id' => $store->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to activate user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate user in store.
     */
    public function deactivate(Store $store, User $user): JsonResponse
    {
        try {
            $store->users()->updateExistingPivot($user->id, ['is_active' => false]);

            $this->logActivity('store_user_deactivated', [
                'store_id' => $store->id,
                'target_user_id' => $user->id,
                'deactivated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User deactivated successfully.'
            ]);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'deactivate_store_user',
                'store_id' => $store->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate user: ' . $e->getMessage()
            ], 500);
        }
    }
}
