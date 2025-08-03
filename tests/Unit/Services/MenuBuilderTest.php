<?php

namespace Tests\Unit\Services;

use App\Services\MenuBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Tests\TestCase;

class MenuBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Store $store;
    protected MenuBuilder $menuBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->store = Store::factory()->create(['name' => 'Test Store']);

        $this->user->stores()->attach($this->store->id, [
            'role' => 'admin',
            'is_active' => true,
            'joined_at' => now()
        ]);

        $this->user->setCurrentStore($this->store->id);
        $this->menuBuilder = new MenuBuilder();
    }

    /** @test */
    public function it_can_build_menu_structure()
    {
        $this->actingAs($this->user);

        $menu = $this->menuBuilder->buildMenu();

        $this->assertIsArray($menu);
        $this->assertNotEmpty($menu);

        // Check that dashboard is always present
        $dashboardItem = collect($menu)->firstWhere('key', 'dashboard');
        $this->assertNotNull($dashboardItem);
        $this->assertEquals('Dashboard', $dashboardItem['title']);
        $this->assertEquals('home', $dashboardItem['icon']);
        $this->assertEquals('dashboard', $dashboardItem['route']);
    }

    /** @test */
    public function it_includes_all_main_menu_sections()
    {
        $this->actingAs($this->user);

        $menu = $this->menuBuilder->buildMenu();
        $menuKeys = collect($menu)->pluck('key')->toArray();

        $expectedSections = [
            'dashboard',
            'materials',
            'sales',
            'customers',
            'purchasing',
            'employees',
            'financial',
            'reports'
        ];

        foreach ($expectedSections as $section) {
            $this->assertContains($section, $menuKeys, "Menu should contain {$section} section");
        }
    }

    /** @test */
    public function it_builds_menu_items_with_correct_structure()
    {
        $this->actingAs($this->user);

        $menu = $this->menuBuilder->buildMenu();
        $materialsItem = collect($menu)->firstWhere('key', 'materials');

        $this->assertNotNull($materialsItem);
        $this->assertArrayHasKey('key', $materialsItem);
        $this->assertArrayHasKey('title', $materialsItem);
        $this->assertArrayHasKey('icon', $materialsItem);
        $this->assertArrayHasKey('route', $materialsItem);
        $this->assertArrayHasKey('permission', $materialsItem);
        $this->assertArrayHasKey('children', $materialsItem);

        // Check children structure
        if (!empty($materialsItem['children'])) {
            $child = $materialsItem['children'][0];
            $this->assertArrayHasKey('key', $child);
            $this->assertArrayHasKey('title', $child);
            $this->assertArrayHasKey('route', $child);
            $this->assertArrayHasKey('permission', $child);
        }
    }

    /** @test */
    public function it_filters_menu_items_by_permissions()
    {
        // Create user with no permissions
        $limitedUser = User::factory()->create();
        $limitedUser->stores()->attach($this->store->id, [
            'role' => 'guest',
            'is_active' => true,
            'joined_at' => now()
        ]);
        $limitedUser->setCurrentStore($this->store->id);

        // Mock permission check to return false for all permissions
        $limitedUser->shouldReceive('hasPermissionInStore')
            ->andReturn(false);

        $this->actingAs($limitedUser);

        $menu = $this->menuBuilder->buildMenu();

        // Should only contain dashboard (no permission required)
        $this->assertCount(1, $menu);
        $this->assertEquals('dashboard', $menu[0]['key']);
    }

    /** @test */
    public function it_generates_breadcrumbs_correctly()
    {
        $this->actingAs($this->user);

        // Test dashboard breadcrumb
        $breadcrumbs = $this->menuBuilder->generateBreadcrumbs('dashboard');
        $this->assertCount(1, $breadcrumbs);
        $this->assertEquals('Dashboard', $breadcrumbs[0]['title']);
        $this->assertTrue($breadcrumbs[0]['active']);

        // Test nested route breadcrumb
        $breadcrumbs = $this->menuBuilder->generateBreadcrumbs('materials.categories.index');
        $this->assertGreaterThan(1, count($breadcrumbs));
        $this->assertEquals('Dashboard', $breadcrumbs[0]['title']);
        $this->assertFalse($breadcrumbs[0]['active']);
    }

    /** @test */
    public function it_handles_unknown_routes_in_breadcrumbs()
    {
        $this->actingAs($this->user);

        $breadcrumbs = $this->menuBuilder->generateBreadcrumbs('unknown.route');
        
        // Should still return dashboard
        $this->assertCount(1, $breadcrumbs);
        $this->assertEquals('Dashboard', $breadcrumbs[0]['title']);
    }

    /** @test */
    public function it_finds_routes_in_menu_structure()
    {
        $this->actingAs($this->user);

        $reflection = new \ReflectionClass($this->menuBuilder);
        $method = $reflection->getMethod('findRouteInMenu');
        $method->setAccessible(true);

        $menuStructure = [
            [
                'key' => 'materials',
                'title' => 'Materials',
                'route' => null,
                'children' => [
                    [
                        'key' => 'materials.catalog',
                        'title' => 'Catalog',
                        'route' => 'materials.index',
                        'children' => []
                    ]
                ]
            ]
        ];

        $result = $method->invoke($this->menuBuilder, 'materials.index', $menuStructure);
        
        $this->assertNotNull($result);
        $this->assertCount(2, $result); // Parent and child
        $this->assertEquals('materials', $result[0]['key']);
        $this->assertEquals('materials.catalog', $result[1]['key']);
    }

    /** @test */
    public function it_checks_permissions_correctly()
    {
        $this->actingAs($this->user);

        $reflection = new \ReflectionClass($this->menuBuilder);
        $method = $reflection->getMethod('hasPermission');
        $method->setAccessible(true);

        // Test null permission (should return true)
        $this->assertTrue($method->invoke($this->menuBuilder, null));

        // Test with permission when user has current store
        $this->user->shouldReceive('hasPermissionInStore')
            ->with($this->store->id, 'materials.view')
            ->andReturn(true);

        $this->assertTrue($method->invoke($this->menuBuilder, 'materials.view'));
    }

    /** @test */
    public function it_returns_false_for_permissions_without_current_store()
    {
        $userWithoutStore = User::factory()->create();
        $this->actingAs($userWithoutStore);

        $reflection = new \ReflectionClass($this->menuBuilder);
        $method = $reflection->getMethod('hasPermission');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($this->menuBuilder, 'materials.view'));
    }

    /** @test */
    public function it_builds_children_recursively()
    {
        $this->actingAs($this->user);

        $menu = $this->menuBuilder->buildMenu();
        $materialsItem = collect($menu)->firstWhere('key', 'materials');

        $this->assertNotNull($materialsItem);
        $this->assertNotEmpty($materialsItem['children']);

        // Check that children have the correct structure
        foreach ($materialsItem['children'] as $child) {
            $this->assertArrayHasKey('key', $child);
            $this->assertArrayHasKey('title', $child);
            $this->assertArrayHasKey('route', $child);
            $this->assertArrayHasKey('permission', $child);
            $this->assertArrayHasKey('children', $child);
        }
    }

    /** @test */
    public function it_supports_menu_badges()
    {
        $this->actingAs($this->user);

        // Test that badge structure is preserved when present
        $reflection = new \ReflectionClass($this->menuBuilder);
        $method = $reflection->getMethod('buildMenuItem');
        $method->setAccessible(true);

        $itemWithBadge = [
            'key' => 'test',
            'title' => 'Test',
            'icon' => 'test',
            'route' => 'test.route',
            'permission' => null,
            'badge' => ['text' => '5', 'color' => 'danger'],
            'children' => []
        ];

        $result = $method->invoke($this->menuBuilder, $itemWithBadge);

        $this->assertArrayHasKey('badge', $result);
        $this->assertEquals('5', $result['badge']['text']);
        $this->assertEquals('danger', $result['badge']['color']);
    }

    /** @test */
    public function it_works_without_authenticated_user()
    {
        $menu = $this->menuBuilder->buildMenu();

        // Should return empty menu or only public items
        $this->assertIsArray($menu);
        
        // Dashboard should be available (no permission required)
        if (!empty($menu)) {
            $dashboardItem = collect($menu)->firstWhere('key', 'dashboard');
            $this->assertNotNull($dashboardItem);
        }
    }
}