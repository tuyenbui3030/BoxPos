<?php

namespace Packages\Common\Tests\Integration;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Packages\Common\Middleware\StoreContextMiddleware;
use Packages\Common\Services\StoreService;
use Packages\Common\Repositories\UserStoreRepository;
use Packages\Common\Tests\TestCase;

class StoreContextTest extends TestCase
{
    use RefreshDatabase;

    protected StoreService $storeService;
    protected UserStoreRepository $userStoreRepository;
    protected User $user;
    protected Store $store1;
    protected Store $store2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userStoreRepository = new UserStoreRepository();
        $this->storeService = new StoreService($this->userStoreRepository);

        // Create test user and stores
        $this->user = User::factory()->create();
        $this->store1 = Store::factory()->create(['name' => 'Store 1']);
        $this->store2 = Store::factory()->create(['name' => 'Store 2']);
    }

    public function test_user_can_access_authorized_store(): void
    {
        // Grant access to store1
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store1->id, ['read', 'write']);

        $hasAccess = $this->storeService->hasStoreAccess($this->user, $this->store1->id);

        $this->assertTrue($hasAccess);
    }

    public function test_user_cannot_access_unauthorized_store(): void
    {
        $hasAccess = $this->storeService->hasStoreAccess($this->user, $this->store1->id);

        $this->assertFalse($hasAccess);
    }

    public function test_user_can_switch_to_authorized_store(): void
    {
        // Grant access to both stores
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store1->id, ['read']);
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store2->id, ['read']);

        // Set current store
        $this->user->update(['current_store_id' => $this->store1->id]);

        Auth::login($this->user);

        $result = $this->storeService->switchStore($this->store2->id);

        $this->assertTrue($result);
        $this->assertEquals($this->store2->id, $this->user->fresh()->current_store_id);
        $this->assertEquals($this->store2->id, $this->storeService->getCurrentStore()->id);
    }

    public function test_user_cannot_switch_to_unauthorized_store(): void
    {
        // Grant access only to store1
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store1->id, ['read']);
        $this->user->update(['current_store_id' => $this->store1->id]);

        Auth::login($this->user);

        $result = $this->storeService->switchStore($this->store2->id);

        $this->assertFalse($result);
        $this->assertEquals($this->store1->id, $this->user->fresh()->current_store_id);
    }

    public function test_get_user_stores_returns_only_accessible_stores(): void
    {
        // Grant access to store1 only
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store1->id, ['read']);

        $userStores = $this->storeService->getUserStores($this->user);

        $this->assertCount(1, $userStores);
        $this->assertEquals($this->store1->id, $userStores->first()->id);
    }

    public function test_grant_store_access_works_correctly(): void
    {
        $permissions = ['read', 'write', 'delete'];

        $result = $this->storeService->grantStoreAccess($this->user, $this->store1->id, $permissions);

        $this->assertTrue($result);
        $this->assertTrue($this->storeService->hasStoreAccess($this->user, $this->store1->id));

        $userPermissions = $this->storeService->getUserStorePermissions($this->user, $this->store1->id);
        $this->assertEquals($permissions, $userPermissions);
    }

    public function test_revoke_store_access_works_correctly(): void
    {
        // First grant access
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store1->id, ['read']);
        $this->user->update(['current_store_id' => $this->store1->id]);

        // Then revoke it
        $result = $this->storeService->revokeStoreAccess($this->user, $this->store1->id);

        $this->assertTrue($result);
        $this->assertFalse($this->storeService->hasStoreAccess($this->user, $this->store1->id));
        $this->assertNull($this->user->fresh()->current_store_id);
    }

    public function test_update_store_permissions_works_correctly(): void
    {
        // Grant initial access
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store1->id, ['read']);

        $newPermissions = ['read', 'write', 'admin'];
        $result = $this->storeService->updateStorePermissions($this->user, $this->store1->id, $newPermissions);

        $this->assertTrue($result);

        $userPermissions = $this->storeService->getUserStorePermissions($this->user, $this->store1->id);
        $this->assertEquals($newPermissions, $userPermissions);
    }

    public function test_has_store_permission_works_correctly(): void
    {
        $permissions = ['read', 'write'];
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store1->id, $permissions);

        $this->assertTrue($this->storeService->hasStorePermission($this->user, $this->store1->id, 'read'));
        $this->assertTrue($this->storeService->hasStorePermission($this->user, $this->store1->id, 'write'));
        $this->assertFalse($this->storeService->hasStorePermission($this->user, $this->store1->id, 'delete'));
    }

    public function test_middleware_redirects_when_no_current_store(): void
    {
        $this->user->update(['current_store_id' => null]);
        Auth::login($this->user);

        $request = Request::create('/test');
        $middleware = new StoreContextMiddleware($this->storeService);

        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContains('stores.select', $response->getTargetUrl());
    }

    public function test_middleware_redirects_when_unauthorized_store(): void
    {
        // Set current store without granting access
        $this->user->update(['current_store_id' => $this->store1->id]);
        Auth::login($this->user);

        $request = Request::create('/test');
        $middleware = new StoreContextMiddleware($this->storeService);

        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContains('stores.select', $response->getTargetUrl());
        $this->assertNull($this->user->fresh()->current_store_id);
    }

    public function test_middleware_allows_access_when_authorized(): void
    {
        // Grant access and set current store
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store1->id, ['read']);
        $this->user->update(['current_store_id' => $this->store1->id]);
        Auth::login($this->user);

        $request = Request::create('/test');
        $middleware = new StoreContextMiddleware($this->storeService);

        $response = $middleware->handle($request, function ($req) {
            $this->assertEquals($this->store1->id, $req->get('current_store_id'));
            $this->assertInstanceOf(Store::class, $req->get('current_store'));
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_validate_store_context_works_correctly(): void
    {
        // Test without authentication
        $this->assertFalse($this->storeService->validateStoreContext());

        // Test with authentication but no current store
        Auth::login($this->user);
        $this->assertFalse($this->storeService->validateStoreContext());

        // Test with authentication and unauthorized store
        $this->user->update(['current_store_id' => $this->store1->id]);
        $this->assertFalse($this->storeService->validateStoreContext());

        // Test with authentication and authorized store
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store1->id, ['read']);
        $this->assertTrue($this->storeService->validateStoreContext());
    }

    public function test_bulk_operations_work_correctly(): void
    {
        $user2 = User::factory()->create();
        $userIds = [$this->user->id, $user2->id];
        $permissions = ['read', 'write'];

        // Test bulk grant
        $result = $this->userStoreRepository->bulkGrantStoreAccess($userIds, $this->store1->id, $permissions);
        $this->assertTrue($result);

        // Verify both users have access
        $this->assertTrue($this->storeService->hasStoreAccess($this->user, $this->store1->id));
        $this->assertTrue($this->storeService->hasStoreAccess($user2, $this->store1->id));

        // Test bulk revoke
        $result = $this->userStoreRepository->bulkRevokeStoreAccess($userIds, $this->store1->id);
        $this->assertTrue($result);

        // Verify both users lost access
        $this->assertFalse($this->storeService->hasStoreAccess($this->user, $this->store1->id));
        $this->assertFalse($this->storeService->hasStoreAccess($user2, $this->store1->id));
    }
}