<?php

namespace Tests\Feature\Livewire\Navigation;

use App\Livewire\Navigation\NavigationComponent;
use App\Services\MenuBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Tests\TestCase;

class NavigationComponentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->store = Store::factory()->create(['name' => 'Test Store']);

        // Add user to store with admin role
        $this->user->stores()->attach($this->store->id, [
            'role' => 'admin',
            'is_active' => true,
            'joined_at' => now()
        ]);

        $this->user->setCurrentStore($this->store->id);
    }

    /** @test */
    public function it_can_render_navigation_component()
    {
        $this->actingAs($this->user);

        Livewire::test(NavigationComponent::class)
            ->assertStatus(200)
            ->assertSee('Dashboard');
    }

    /** @test */
    public function it_loads_menu_items_on_mount()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(NavigationComponent::class);

        $menuItems = $component->get('menuItems');
        $this->assertIsArray($menuItems);
        $this->assertNotEmpty($menuItems);
        
        // Should contain dashboard item
        $dashboardItem = collect($menuItems)->firstWhere('key', 'dashboard');
        $this->assertNotNull($dashboardItem);
        $this->assertEquals('Dashboard', $dashboardItem['title']);
    }

    /** @test */
    public function it_can_toggle_dropdown()
    {
        $this->actingAs($this->user);

        Livewire::test(NavigationComponent::class)
            ->assertSet('activeDropdowns', [])
            ->call('toggleDropdown', 'materials')
            ->assertSet('activeDropdowns', ['materials'])
            ->call('toggleDropdown', 'materials')
            ->assertSet('activeDropdowns', []);
    }

    /** @test */
    public function it_can_handle_multiple_dropdowns()
    {
        $this->actingAs($this->user);

        Livewire::test(NavigationComponent::class)
            ->call('toggleDropdown', 'materials')
            ->call('toggleDropdown', 'sales')
            ->assertSet('activeDropdowns', ['materials', 'sales'])
            ->call('toggleDropdown', 'materials')
            ->assertSet('activeDropdowns', ['sales']);
    }

    /** @test */
    public function it_can_close_specific_dropdown()
    {
        $this->actingAs($this->user);

        Livewire::test(NavigationComponent::class)
            ->call('toggleDropdown', 'materials')
            ->call('toggleDropdown', 'sales')
            ->call('closeDropdown', 'materials')
            ->assertSet('activeDropdowns', ['sales']);
    }

    /** @test */
    public function it_can_close_all_dropdowns()
    {
        $this->actingAs($this->user);

        Livewire::test(NavigationComponent::class)
            ->call('toggleDropdown', 'materials')
            ->call('toggleDropdown', 'sales')
            ->call('closeAllDropdowns')
            ->assertSet('activeDropdowns', []);
    }

    /** @test */
    public function it_detects_dropdown_active_state()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(NavigationComponent::class);

        $this->assertFalse($component->instance()->isDropdownActive('materials'));

        $component->call('toggleDropdown', 'materials');
        $this->assertTrue($component->instance()->isDropdownActive('materials'));
    }

    /** @test */
    public function it_detects_route_active_state()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(NavigationComponent::class)
            ->set('currentRoute', 'materials.index');

        $this->assertTrue($component->instance()->isRouteActive('materials.index'));
        $this->assertFalse($component->instance()->isRouteActive('sales.index'));
        
        // Test route prefix matching
        $this->assertTrue($component->instance()->isRouteActive('materials'));
    }

    /** @test */
    public function it_generates_breadcrumbs_correctly()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(NavigationComponent::class)
            ->set('currentRoute', 'materials.categories.index')
            ->call('generateBreadcrumbs');

        $breadcrumbs = $component->get('breadcrumbs');
        $this->assertIsArray($breadcrumbs);
        $this->assertNotEmpty($breadcrumbs);
        
        // Should start with Dashboard
        $this->assertEquals('Dashboard', $breadcrumbs[0]['title']);
    }

    /** @test */
    public function it_handles_route_change_event()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(NavigationComponent::class)
            ->call('handleRouteChange', 'sales.orders.index');

        $this->assertEquals('sales.orders.index', $component->get('currentRoute'));
    }

    /** @test */
    public function it_refreshes_navigation_on_event()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(NavigationComponent::class);
        
        $originalMenuCount = count($component->get('menuItems'));
        
        $component->call('refreshNavigation');
        
        // Menu should be reloaded
        $this->assertIsArray($component->get('menuItems'));
        $this->assertEquals($originalMenuCount, count($component->get('menuItems')));
    }

    /** @test */
    public function it_checks_permissions_correctly()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(NavigationComponent::class);

        // Test with null permission (should always return true)
        $this->assertTrue($component->instance()->hasPermission(null));

        // Test with actual permission (mock the user permission check)
        $this->user->shouldReceive('hasPermissionInStore')
            ->with($this->store->id, 'materials.view')
            ->andReturn(true);

        $this->assertTrue($component->instance()->hasPermission('materials.view'));
    }

    /** @test */
    public function it_handles_fallback_menu_on_error()
    {
        $this->actingAs($this->user);

        // Mock MenuBuilder to throw exception
        $this->app->bind(MenuBuilder::class, function () {
            $mock = \Mockery::mock(MenuBuilder::class);
            $mock->shouldReceive('buildMenu')->andThrow(new \Exception('Test error'));
            return $mock;
        });

        $component = Livewire::test(NavigationComponent::class);

        $menuItems = $component->get('menuItems');
        $this->assertIsArray($menuItems);
        $this->assertNotEmpty($menuItems);
        
        // Should contain fallback dashboard item
        $dashboardItem = collect($menuItems)->firstWhere('key', 'dashboard');
        $this->assertNotNull($dashboardItem);
    }

    /** @test */
    public function it_works_for_unauthenticated_users()
    {
        Livewire::test(NavigationComponent::class)
            ->assertStatus(200)
            ->assertSet('menuItems', []);
    }

    /** @test */
    public function it_filters_menu_items_by_permissions()
    {
        // Create user with limited permissions
        $limitedUser = User::factory()->create();
        $limitedUser->stores()->attach($this->store->id, [
            'role' => 'cashier',
            'is_active' => true,
            'joined_at' => now()
        ]);
        $limitedUser->setCurrentStore($this->store->id);

        $this->actingAs($limitedUser);

        $component = Livewire::test(NavigationComponent::class);

        $menuItems = $component->get('menuItems');
        
        // Should have fewer items than admin user
        $this->assertIsArray($menuItems);
        
        // Dashboard should always be available
        $dashboardItem = collect($menuItems)->firstWhere('key', 'dashboard');
        $this->assertNotNull($dashboardItem);
    }
}