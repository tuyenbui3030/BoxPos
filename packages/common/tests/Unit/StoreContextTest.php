<?php

namespace Packages\Common\Tests\Unit;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Packages\Common\Helpers\StoreContext;
use Packages\Common\Repositories\UserStoreRepository;
use Packages\Common\Services\StoreService;
use Packages\Common\Tests\TestCase;

class StoreContextTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Store $store;
    protected UserStoreRepository $userStoreRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->store = Store::factory()->create();
        $this->userStoreRepository = new UserStoreRepository();
    }

    public function test_get_current_store_id_returns_null_when_not_authenticated(): void
    {
        $this->assertNull(StoreContext::getCurrentStoreId());
    }

    public function test_get_current_store_id_returns_user_store_id_when_authenticated(): void
    {
        $this->user->update(['current_store_id' => $this->store->id]);
        Auth::login($this->user);

        $this->assertEquals($this->store->id, StoreContext::getCurrentStoreId());
    }

    public function test_has_current_store_access_returns_false_when_not_authenticated(): void
    {
        $this->assertFalse(StoreContext::hasCurrentStoreAccess());
    }

    public function test_has_current_store_access_returns_false_when_no_current_store(): void
    {
        Auth::login($this->user);

        $this->assertFalse(StoreContext::hasCurrentStoreAccess());
    }

    public function test_has_current_store_access_returns_true_when_user_has_access(): void
    {
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store->id, ['read']);
        $this->user->update(['current_store_id' => $this->store->id]);
        Auth::login($this->user);

        $this->assertTrue(StoreContext::hasCurrentStoreAccess());
    }

    public function test_get_user_stores_returns_empty_collection_when_not_authenticated(): void
    {
        $stores = StoreContext::getUserStores();

        $this->assertTrue($stores->isEmpty());
    }

    public function test_get_user_stores_returns_accessible_stores_when_authenticated(): void
    {
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store->id, ['read']);
        Auth::login($this->user);

        $stores = StoreContext::getUserStores();

        $this->assertCount(1, $stores);
        $this->assertEquals($this->store->id, $stores->first()->id);
    }

    public function test_switch_store_returns_false_when_not_authenticated(): void
    {
        $result = StoreContext::switchStore($this->store->id);

        $this->assertFalse($result);
    }

    public function test_switch_store_works_when_authenticated_and_has_access(): void
    {
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store->id, ['read']);
        Auth::login($this->user);

        $result = StoreContext::switchStore($this->store->id);

        $this->assertTrue($result);
        $this->assertEquals($this->store->id, $this->user->fresh()->current_store_id);
    }

    public function test_has_permission_returns_false_when_not_authenticated(): void
    {
        $this->assertFalse(StoreContext::hasPermission('read'));
    }

    public function test_has_permission_returns_false_when_no_current_store(): void
    {
        Auth::login($this->user);

        $this->assertFalse(StoreContext::hasPermission('read'));
    }

    public function test_has_permission_returns_true_when_user_has_permission(): void
    {
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store->id, ['read', 'write']);
        $this->user->update(['current_store_id' => $this->store->id]);
        Auth::login($this->user);

        $this->assertTrue(StoreContext::hasPermission('read'));
        $this->assertTrue(StoreContext::hasPermission('write'));
        $this->assertFalse(StoreContext::hasPermission('admin'));
    }

    public function test_get_permissions_returns_empty_array_when_not_authenticated(): void
    {
        $permissions = StoreContext::getPermissions();

        $this->assertEmpty($permissions);
    }

    public function test_get_permissions_returns_user_permissions_when_authenticated(): void
    {
        $expectedPermissions = ['read', 'write', 'manage_inventory'];
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store->id, $expectedPermissions);
        $this->user->update(['current_store_id' => $this->store->id]);
        Auth::login($this->user);

        $permissions = StoreContext::getPermissions();

        $this->assertEquals($expectedPermissions, $permissions);
    }

    public function test_is_store_admin_returns_true_when_user_has_admin_permission(): void
    {
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store->id, ['admin']);
        $this->user->update(['current_store_id' => $this->store->id]);
        Auth::login($this->user);

        $this->assertTrue(StoreContext::isStoreAdmin());
    }

    public function test_is_store_admin_returns_true_when_user_has_store_admin_permission(): void
    {
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store->id, ['store_admin']);
        $this->user->update(['current_store_id' => $this->store->id]);
        Auth::login($this->user);

        $this->assertTrue(StoreContext::isStoreAdmin());
    }

    public function test_is_store_admin_returns_false_when_user_has_no_admin_permissions(): void
    {
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store->id, ['read', 'write']);
        $this->user->update(['current_store_id' => $this->store->id]);
        Auth::login($this->user);

        $this->assertFalse(StoreContext::isStoreAdmin());
    }

    public function test_can_manage_users_returns_true_for_admin(): void
    {
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store->id, ['admin']);
        $this->user->update(['current_store_id' => $this->store->id]);
        Auth::login($this->user);

        $this->assertTrue(StoreContext::canManageUsers());
    }

    public function test_can_manage_users_returns_true_for_manage_users_permission(): void
    {
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store->id, ['manage_users']);
        $this->user->update(['current_store_id' => $this->store->id]);
        Auth::login($this->user);

        $this->assertTrue(StoreContext::canManageUsers());
    }

    public function test_can_manage_inventory_returns_true_for_manage_inventory_permission(): void
    {
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store->id, ['manage_inventory']);
        $this->user->update(['current_store_id' => $this->store->id]);
        Auth::login($this->user);

        $this->assertTrue(StoreContext::canManageInventory());
    }

    public function test_can_process_sales_returns_true_for_process_sales_permission(): void
    {
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store->id, ['process_sales']);
        $this->user->update(['current_store_id' => $this->store->id]);
        Auth::login($this->user);

        $this->assertTrue(StoreContext::canProcessSales());
    }

    public function test_can_view_reports_returns_true_for_view_reports_permission(): void
    {
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store->id, ['view_reports']);
        $this->user->update(['current_store_id' => $this->store->id]);
        Auth::login($this->user);

        $this->assertTrue(StoreContext::canViewReports());
    }

    public function test_can_manage_finances_returns_true_for_manage_finances_permission(): void
    {
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store->id, ['manage_finances']);
        $this->user->update(['current_store_id' => $this->store->id]);
        Auth::login($this->user);

        $this->assertTrue(StoreContext::canManageFinances());
    }

    public function test_validate_returns_false_when_not_authenticated(): void
    {
        $this->assertFalse(StoreContext::validate());
    }

    public function test_validate_returns_true_when_valid_store_context(): void
    {
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store->id, ['read']);
        $this->user->update(['current_store_id' => $this->store->id]);
        Auth::login($this->user);

        $this->assertTrue(StoreContext::validate());
    }

    public function test_get_logging_context_returns_proper_context(): void
    {
        $this->userStoreRepository->grantStoreAccess($this->user->id, $this->store->id, ['read', 'write']);
        $this->user->update(['current_store_id' => $this->store->id]);
        Auth::login($this->user);

        $context = StoreContext::getLoggingContext();

        $this->assertArrayHasKey('store_id', $context);
        $this->assertArrayHasKey('store_name', $context);
        $this->assertArrayHasKey('user_id', $context);
        $this->assertArrayHasKey('user_permissions', $context);

        $this->assertEquals($this->store->id, $context['store_id']);
        $this->assertEquals($this->store->name, $context['store_name']);
        $this->assertEquals($this->user->id, $context['user_id']);
        $this->assertEquals(['read', 'write'], $context['user_permissions']);
    }
}