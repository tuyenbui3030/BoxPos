<?php

namespace Tests\Feature\Livewire\Header;

use App\Livewire\Header\HeaderComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Tests\TestCase;

class HeaderComponentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Store $store1;
    protected Store $store2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->store1 = Store::factory()->create(['name' => 'Store One']);
        $this->store2 = Store::factory()->create(['name' => 'Store Two']);

        // Add user to both stores
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

        $this->user->setCurrentStore($this->store1->id);
    }

    /** @test */
    public function it_can_render_header_component()
    {
        $this->actingAs($this->user);

        Livewire::test(HeaderComponent::class)
            ->assertStatus(200)
            ->assertSee($this->store1->name);
    }

    /** @test */
    public function it_loads_store_data_on_mount()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(HeaderComponent::class);

        $this->assertEquals($this->store1->id, $component->get('currentStore')->id);
        $this->assertCount(2, $component->get('availableStores'));
    }

    /** @test */
    public function it_can_toggle_mobile_menu()
    {
        $this->actingAs($this->user);

        Livewire::test(HeaderComponent::class)
            ->assertSet('showMobileMenu', false)
            ->call('toggleMobileMenu')
            ->assertSet('showMobileMenu', true)
            ->call('toggleMobileMenu')
            ->assertSet('showMobileMenu', false);
    }

    /** @test */
    public function it_can_close_mobile_menu()
    {
        $this->actingAs($this->user);

        Livewire::test(HeaderComponent::class)
            ->set('showMobileMenu', true)
            ->call('closeMobileMenu')
            ->assertSet('showMobileMenu', false);
    }

    /** @test */
    public function it_handles_store_switch_event()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(HeaderComponent::class);

        // Switch to store2
        $this->user->setCurrentStore($this->store2->id);

        $component->call('handleStoreSwitch', $this->store2->id);

        $this->assertEquals($this->store2->id, $component->get('currentStore')->id);
    }

    /** @test */
    public function it_returns_correct_logo_url()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(HeaderComponent::class);

        // Test default logo when store has no logo
        $this->assertStringContains('boxpos-logo.png', $component->get('logoUrl'));

        // Test store logo when available
        $this->store1->update(['logo' => 'logos/store1.png']);
        $this->user->refresh();
        
        $component->call('loadStoreData');
        $this->assertStringContains('storage/logos/store1.png', $component->get('logoUrl'));
    }

    /** @test */
    public function it_returns_correct_store_name()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(HeaderComponent::class);

        $this->assertEquals($this->store1->name, $component->get('storeName'));

        // Test fallback when no current store
        $this->user->clearCurrentStore();
        $component->call('loadStoreData');
        
        $this->assertEquals(config('app.name', 'BoxPos'), $component->get('storeName'));
    }

    /** @test */
    public function it_works_for_unauthenticated_users()
    {
        Livewire::test(HeaderComponent::class)
            ->assertStatus(200)
            ->assertSet('currentStore', null)
            ->assertSet('availableStores', []);
    }

    /** @test */
    public function mobile_menu_responds_to_events()
    {
        $this->actingAs($this->user);

        Livewire::test(HeaderComponent::class)
            ->assertSet('showMobileMenu', false)
            ->dispatch('mobile-menu-toggle')
            ->assertSet('showMobileMenu', true);
    }
}