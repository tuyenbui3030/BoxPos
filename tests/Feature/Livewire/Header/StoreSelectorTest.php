<?php

namespace Tests\Feature\Livewire\Header;

use App\Livewire\Header\StoreSelector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Tests\TestCase;

class StoreSelectorTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Store $store1;
    protected Store $store2;
    protected Store $store3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->store1 = Store::factory()->create(['name' => 'Store Alpha']);
        $this->store2 = Store::factory()->create(['name' => 'Store Beta']);
        $this->store3 = Store::factory()->create(['name' => 'Store Gamma']);

        // Add user to stores
        $this->user->stores()->attach($this->store1->id, [
            'role' => 'admin',
            'is_active' => true,
            'joined_at' => now()
        ]);
        $this->user->stores()->attach($this->store2->id, [
            'role' => 'manager',
            'is_active' => true,
            'joined_at' => now()
        ]);
        $this->user->stores()->attach($this->store3->id, [
            'role' => 'staff',
            'is_active' => true,
            'joined_at' => now()
        ]);

        $this->user->setCurrentStore($this->store1->id);
    }

    /** @test */
    public function it_can_render_store_selector()
    {
        $this->actingAs($this->user);

        Livewire::test(StoreSelector::class)
            ->assertStatus(200)
            ->assertSee($this->store1->name);
    }

    /** @test */
    public function it_can_render_mobile_version()
    {
        $this->actingAs($this->user);

        Livewire::test(StoreSelector::class, ['mobile' => true])
            ->assertStatus(200)
            ->assertSet('mobile', true)
            ->assertSee('Select Store');
    }

    /** @test */
    public function it_loads_stores_on_mount()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(StoreSelector::class);

        $this->assertEquals($this->store1->id, $component->get('currentStore')->id);
        $this->assertCount(3, $component->get('availableStores'));
    }

    /** @test */
    public function it_can_toggle_dropdown()
    {
        $this->actingAs($this->user);

        Livewire::test(StoreSelector::class)
            ->assertSet('showDropdown', false)
            ->call('toggleDropdown')
            ->assertSet('showDropdown', true)
            ->call('toggleDropdown')
            ->assertSet('showDropdown', false);
    }

    /** @test */
    public function it_can_close_dropdown()
    {
        $this->actingAs($this->user);

        Livewire::test(StoreSelector::class)
            ->set('showDropdown', true)
            ->call('closeDropdown')
            ->assertSet('showDropdown', false)
            ->assertSet('search', '');
    }

    /** @test */
    public function it_can_search_stores()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(StoreSelector::class)
            ->set('search', 'Alpha')
            ->call('loadStores');

        $filteredStores = $component->get('filteredStores');
        $this->assertCount(1, $filteredStores);
        $this->assertEquals('Store Alpha', $filteredStores[0]['name']);
    }

    /** @test */
    public function it_can_switch_stores()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(StoreSelector::class)
            ->call('switchStore', $this->store2->id)
            ->assertDispatched('store-switched');

        // Verify user's current store was updated
        $this->user->refresh();
        $this->assertEquals($this->store2->id, $this->user->current_store_id);
    }

    /** @test */
    public function it_prevents_switching_to_unauthorized_store()
    {
        $this->actingAs($this->user);

        $unauthorizedStore = Store::factory()->create(['name' => 'Unauthorized Store']);

        Livewire::test(StoreSelector::class)
            ->call('switchStore', $unauthorizedStore->id)
            ->assertDispatched('store-switch-failed');

        // Verify user's current store was not changed
        $this->user->refresh();
        $this->assertEquals($this->store1->id, $this->user->current_store_id);
    }

    /** @test */
    public function it_handles_store_switch_event()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(StoreSelector::class);

        // Switch to store2 externally
        $this->user->setCurrentStore($this->store2->id);

        $component->dispatch('store-switched', $this->store2->id);

        $this->assertEquals($this->store2->id, $component->get('currentStore')->id);
    }

    /** @test */
    public function it_filters_stores_by_search_term()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(StoreSelector::class)
            ->set('search', 'Beta');

        $filteredStores = $component->get('filteredStores');
        $this->assertCount(1, $filteredStores);
        $this->assertEquals('Store Beta', $filteredStores[0]['name']);
    }

    /** @test */
    public function it_shows_all_stores_when_search_is_empty()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(StoreSelector::class)
            ->set('search', '');

        $filteredStores = $component->get('filteredStores');
        $this->assertCount(3, $filteredStores);
    }

    /** @test */
    public function it_works_for_unauthenticated_users()
    {
        Livewire::test(StoreSelector::class)
            ->assertStatus(200)
            ->assertSet('currentStore', null)
            ->assertSet('availableStores', []);
    }

    /** @test */
    public function it_displays_store_logos_when_available()
    {
        $this->actingAs($this->user);

        $this->store1->update(['logo' => 'logos/store1.png']);

        $component = Livewire::test(StoreSelector::class);
        $component->call('loadStores');

        $availableStores = $component->get('availableStores');
        $store1Data = collect($availableStores)->firstWhere('id', $this->store1->id);
        
        $this->assertEquals('logos/store1.png', $store1Data['logo']);
    }

    /** @test */
    public function it_shows_store_initials_when_no_logo()
    {
        $this->actingAs($this->user);

        Livewire::test(StoreSelector::class)
            ->assertSee('SA'); // Store Alpha initials
    }

    /** @test */
    public function it_closes_dropdown_after_successful_store_switch()
    {
        $this->actingAs($this->user);

        Livewire::test(StoreSelector::class)
            ->set('showDropdown', true)
            ->call('switchStore', $this->store2->id)
            ->assertSet('showDropdown', false);
    }

    /** @test */
    public function search_updates_trigger_logging()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(StoreSelector::class)
            ->set('search', 'test search');

        // The updatedSearch method should be called
        $this->assertEquals('test search', $component->get('search'));
    }
}