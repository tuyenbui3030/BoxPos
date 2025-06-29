<?php

namespace Packages\Store\Tests\Feature;

use Tests\TestCase;
use Packages\User\Models\User;
use Packages\Store\Models\Store;
use Livewire\Livewire;
use Packages\Store\Livewire\StoreSelection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test stores
        $this->store1 = Store::create([
            'name' => 'Test Store 1',
            'slug' => 'test-store-1',
            'status' => Store::STATUS_ACTIVE,
            'timezone' => 'Asia/Ho_Chi_Minh',
            'currency' => 'VND',
            'language' => 'vi',
        ]);

        $this->store2 = Store::create([
            'name' => 'Test Store 2',
            'slug' => 'test-store-2',
            'status' => Store::STATUS_ACTIVE,
            'timezone' => 'Asia/Ho_Chi_Minh',
            'currency' => 'VND',
            'language' => 'vi',
        ]);

        // Create test user
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function it_shows_no_stores_message_when_user_has_no_access()
    {
        $this->actingAs($this->user);

        Livewire::test(StoreSelection::class)
            ->assertSee('No projects available')
            ->assertSee('You don\'t have access to any projects yet');
    }

    /** @test */
    public function it_shows_available_stores_when_user_has_access()
    {
        // Give user access to both stores
        $this->user->stores()->attach($this->store1->id, [
            'role' => 'admin',
            'permissions' => json_encode(['manage_settings']),
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $this->user->stores()->attach($this->store2->id, [
            'role' => 'manager',
            'permissions' => json_encode(['manage_customers']),
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $this->actingAs($this->user);

        Livewire::test(StoreSelection::class)
            ->assertSee('Test Store 1')
            ->assertSee('Test Store 2')
            ->assertSee('admin access')
            ->assertSee('manager access')
            ->assertDontSee('No projects available');
    }

    /** @test */
    public function it_can_select_a_store()
    {
        // Give user access to store
        $this->user->stores()->attach($this->store1->id, [
            'role' => 'admin',
            'permissions' => json_encode(['manage_settings']),
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $this->actingAs($this->user);

        Livewire::test(StoreSelection::class)
            ->call('selectStore', $this->store1->id)
            ->assertRedirect();

        // Check that user's current store was updated
        $this->assertEquals($this->store1->id, $this->user->fresh()->current_store_id);
    }

    /** @test */
    public function it_filters_stores_by_search()
    {
        // Give user access to both stores
        $this->user->stores()->attach($this->store1->id, [
            'role' => 'admin',
            'permissions' => json_encode(['manage_settings']),
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $this->user->stores()->attach($this->store2->id, [
            'role' => 'manager',
            'permissions' => json_encode(['manage_customers']),
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $this->actingAs($this->user);

        Livewire::test(StoreSelection::class)
            ->set('search', 'Store 1')
            ->assertSee('Test Store 1')
            ->assertDontSee('Test Store 2');
    }

    /** @test */
    public function it_shows_search_box_when_more_than_4_stores()
    {
        // Create 5 stores and give user access
        for ($i = 3; $i <= 7; $i++) {
            $store = Store::create([
                'name' => "Test Store {$i}",
                'slug' => "test-store-{$i}",
                'status' => Store::STATUS_ACTIVE,
                'timezone' => 'Asia/Ho_Chi_Minh',
                'currency' => 'VND',
                'language' => 'vi',
            ]);

            $this->user->stores()->attach($store->id, [
                'role' => 'staff',
                'permissions' => json_encode(['view_dashboard']),
                'is_active' => true,
                'joined_at' => now(),
            ]);
        }

        // Also attach the original 2 stores
        $this->user->stores()->attach($this->store1->id, [
            'role' => 'admin',
            'permissions' => json_encode(['manage_settings']),
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $this->user->stores()->attach($this->store2->id, [
            'role' => 'manager',
            'permissions' => json_encode(['manage_customers']),
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $this->actingAs($this->user);

        Livewire::test(StoreSelection::class)
            ->assertSee('Search projects...')
            ->assertSeeHtml('input-icon');
    }
}
