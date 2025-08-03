<?php

namespace App\Livewire\Navigation;

use Livewire\Component;
use Packages\Log\Traits\Loggable;
use App\Services\MenuBuilder;

class NavigationComponent extends Component
{
    use Loggable;

    public $menuItems = [];
    public $activeDropdowns = [];
    public $breadcrumbs = [];
    public $currentRoute = '';

    protected $listeners = [
        'navigation-refresh' => 'refreshNavigation',
        'route-changed' => 'handleRouteChange'
    ];

    public function mount()
    {
        $this->logActivity('navigation_component_mounted');
        $this->loadNavigation();
        $this->currentRoute = request()->route()?->getName() ?? '';
        $this->generateBreadcrumbs();
    }

    public function toggleDropdown($menuKey)
    {
        if (in_array($menuKey, $this->activeDropdowns)) {
            $this->activeDropdowns = array_filter($this->activeDropdowns, fn($key) => $key !== $menuKey);
            $this->logActivity('navigation_dropdown_closed', ['menu_key' => $menuKey]);
        } else {
            $this->activeDropdowns[] = $menuKey;
            $this->logActivity('navigation_dropdown_opened', ['menu_key' => $menuKey]);
        }
    }

    public function closeDropdown($menuKey)
    {
        $this->activeDropdowns = array_filter($this->activeDropdowns, fn($key) => $key !== $menuKey);
        $this->logActivity('navigation_dropdown_closed', ['menu_key' => $menuKey]);
    }

    public function closeAllDropdowns()
    {
        $this->activeDropdowns = [];
        $this->logActivity('navigation_all_dropdowns_closed');
    }

    public function refreshNavigation()
    {
        $this->logActivity('navigation_refresh_requested');
        $this->loadNavigation();
        $this->generateBreadcrumbs();
    }

    public function handleRouteChange($routeName)
    {
        $this->currentRoute = $routeName;
        $this->generateBreadcrumbs();
        $this->logActivity('navigation_route_changed', ['route' => $routeName]);
    }

    protected function loadNavigation()
    {
        try {
            $menuBuilder = app(MenuBuilder::class);
            $this->menuItems = $menuBuilder->buildMenu();
            
            $this->logActivity('navigation_loaded', [
                'menu_items_count' => count($this->menuItems)
            ]);
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'load_navigation'
            ]);
            
            $this->menuItems = $this->getFallbackMenu();
        }
    }

    protected function generateBreadcrumbs()
    {
        try {
            $menuBuilder = app(MenuBuilder::class);
            $this->breadcrumbs = $menuBuilder->generateBreadcrumbs($this->currentRoute);
            
            $this->logActivity('breadcrumbs_generated', [
                'route' => $this->currentRoute,
                'breadcrumbs_count' => count($this->breadcrumbs)
            ]);
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'generate_breadcrumbs',
                'route' => $this->currentRoute
            ]);
            
            $this->breadcrumbs = [];
        }
    }

    protected function getFallbackMenu(): array
    {
        return [
            [
                'key' => 'dashboard',
                'title' => 'Dashboard',
                'icon' => 'home',
                'route' => 'dashboard',
                'permission' => null,
                'children' => []
            ],
            [
                'key' => 'materials',
                'title' => 'Materials',
                'icon' => 'package',
                'route' => null,
                'permission' => 'materials.view',
                'children' => [
                    [
                        'key' => 'materials.catalog',
                        'title' => 'Material Catalog',
                        'route' => 'materials.index',
                        'permission' => 'materials.view'
                    ],
                    [
                        'key' => 'materials.categories',
                        'title' => 'Categories',
                        'route' => 'materials.categories.index',
                        'permission' => 'materials.categories.view'
                    ]
                ]
            ]
        ];
    }

    public function isDropdownActive($menuKey): bool
    {
        return in_array($menuKey, $this->activeDropdowns);
    }

    public function isRouteActive($route): bool
    {
        if (!$route) {
            return false;
        }
        
        return $this->currentRoute === $route || 
               str_starts_with($this->currentRoute, $route . '.');
    }

    public function hasPermission($permission): bool
    {
        if (!$permission || !auth()->check()) {
            return true;
        }

        $user = auth()->user();
        $currentStoreId = $user->current_store_id;
        
        if (!$currentStoreId) {
            return false;
        }

        return $user->hasPermissionInStore($currentStoreId, $permission);
    }

    public function render()
    {
        return view('livewire.navigation.navigation-component');
    }
}