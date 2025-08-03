<?php

namespace App\Services;

use Packages\Log\Traits\Loggable;

class MenuBuilder
{
    use Loggable;

    protected array $menuStructure = [];

    public function __construct()
    {
        $this->initializeMenuStructure();
    }

    /**
     * Build the complete menu based on user permissions
     */
    public function buildMenu(): array
    {
        $this->logActivity('menu_build_started');
        
        $menu = [];
        
        foreach ($this->menuStructure as $item) {
            if ($this->hasPermission($item['permission'])) {
                $menuItem = $this->buildMenuItem($item);
                if ($menuItem) {
                    $menu[] = $menuItem;
                }
            }
        }

        $this->logActivity('menu_built', [
            'total_items' => count($menu)
        ]);

        return $menu;
    }

    /**
     * Generate breadcrumbs for current route
     */
    public function generateBreadcrumbs(string $currentRoute): array
    {
        $breadcrumbs = [];
        
        // Always start with Dashboard
        $breadcrumbs[] = [
            'title' => 'Dashboard',
            'route' => 'dashboard',
            'active' => $currentRoute === 'dashboard'
        ];

        // Find the current route in menu structure
        $routePath = $this->findRouteInMenu($currentRoute, $this->menuStructure);
        
        if ($routePath) {
            foreach ($routePath as $item) {
                if ($item['route'] !== 'dashboard') {
                    $breadcrumbs[] = [
                        'title' => $item['title'],
                        'route' => $item['route'],
                        'active' => $item['route'] === $currentRoute
                    ];
                }
            }
        }

        return $breadcrumbs;
    }

    /**
     * Build a single menu item with children
     */
    protected function buildMenuItem(array $item): ?array
    {
        $menuItem = [
            'key' => $item['key'],
            'title' => $item['title'],
            'icon' => $item['icon'],
            'route' => $item['route'] ?? null,
            'permission' => $item['permission'],
            'children' => [],
            'badge' => $item['badge'] ?? null,
            'description' => $item['description'] ?? null
        ];

        // Build children if they exist
        if (!empty($item['children'])) {
            foreach ($item['children'] as $child) {
                if ($this->hasPermission($child['permission'])) {
                    $childItem = $this->buildMenuItem($child);
                    if ($childItem) {
                        $menuItem['children'][] = $childItem;
                    }
                }
            }
        }

        return $menuItem;
    }

    /**
     * Find route path in menu structure
     */
    protected function findRouteInMenu(string $route, array $menu, array $path = []): ?array
    {
        foreach ($menu as $item) {
            $currentPath = array_merge($path, [$item]);
            
            if ($item['route'] === $route) {
                return $currentPath;
            }
            
            if (!empty($item['children'])) {
                $result = $this->findRouteInMenu($route, $item['children'], $currentPath);
                if ($result) {
                    return $result;
                }
            }
        }
        
        return null;
    }

    /**
     * Check if user has permission
     */
    protected function hasPermission(?string $permission): bool
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

    /**
     * Initialize the menu structure
     */
    protected function initializeMenuStructure(): void
    {
        $this->menuStructure = [
            [
                'key' => 'dashboard',
                'title' => __('Dashboard'),
                'icon' => 'home',
                'route' => 'dashboard',
                'permission' => null,
                'children' => []
            ],
            [
                'key' => 'materials',
                'title' => __('Materials'),
                'icon' => 'package',
                'route' => null,
                'permission' => 'materials.view',
                'children' => [
                    [
                        'key' => 'materials.catalog',
                        'title' => __('Material Catalog'),
                        'icon' => 'list',
                        'route' => 'materials.index',
                        'permission' => 'materials.view',
                        'children' => []
                    ],
                    [
                        'key' => 'materials.categories',
                        'title' => __('Categories'),
                        'icon' => 'folder',
                        'route' => 'materials.categories.index',
                        'permission' => 'materials.categories.view',
                        'children' => []
                    ],
                    [
                        'key' => 'materials.units',
                        'title' => __('Units'),
                        'icon' => 'ruler',
                        'route' => 'materials.units.index',
                        'permission' => 'materials.units.view',
                        'children' => []
                    ],
                    [
                        'key' => 'materials.inventory',
                        'title' => __('Inventory'),
                        'icon' => 'archive',
                        'route' => 'materials.inventory.index',
                        'permission' => 'materials.inventory.view',
                        'children' => []
                    ]
                ]
            ],
            [
                'key' => 'sales',
                'title' => __('Sales'),
                'icon' => 'shopping-cart',
                'route' => null,
                'permission' => 'sales.view',
                'children' => [
                    [
                        'key' => 'sales.orders',
                        'title' => __('Orders'),
                        'icon' => 'file-text',
                        'route' => 'sales.orders.index',
                        'permission' => 'sales.orders.view',
                        'children' => []
                    ],
                    [
                        'key' => 'sales.pos',
                        'title' => __('Point of Sale'),
                        'icon' => 'credit-card',
                        'route' => 'sales.pos.index',
                        'permission' => 'sales.pos.access',
                        'children' => []
                    ],
                    [
                        'key' => 'sales.invoices',
                        'title' => __('Invoices'),
                        'icon' => 'file-invoice',
                        'route' => 'sales.invoices.index',
                        'permission' => 'sales.invoices.view',
                        'children' => []
                    ]
                ]
            ],
            [
                'key' => 'customers',
                'title' => __('Customers'),
                'icon' => 'users',
                'route' => 'customers.index',
                'permission' => 'customers.view',
                'children' => []
            ],
            [
                'key' => 'purchasing',
                'title' => __('Purchasing'),
                'icon' => 'truck',
                'route' => null,
                'permission' => 'purchasing.view',
                'children' => [
                    [
                        'key' => 'purchasing.orders',
                        'title' => __('Purchase Orders'),
                        'icon' => 'file-plus',
                        'route' => 'purchasing.orders.index',
                        'permission' => 'purchasing.orders.view',
                        'children' => []
                    ],
                    [
                        'key' => 'purchasing.suppliers',
                        'title' => __('Suppliers'),
                        'icon' => 'building',
                        'route' => 'purchasing.suppliers.index',
                        'permission' => 'purchasing.suppliers.view',
                        'children' => []
                    ],
                    [
                        'key' => 'purchasing.receipts',
                        'title' => __('Goods Receipts'),
                        'icon' => 'inbox',
                        'route' => 'purchasing.receipts.index',
                        'permission' => 'purchasing.receipts.view',
                        'children' => []
                    ]
                ]
            ],
            [
                'key' => 'employees',
                'title' => __('Employees'),
                'icon' => 'user-check',
                'route' => null,
                'permission' => 'employees.view',
                'children' => [
                    [
                        'key' => 'employees.list',
                        'title' => __('Employee List'),
                        'icon' => 'users',
                        'route' => 'employees.index',
                        'permission' => 'employees.view',
                        'children' => []
                    ],
                    [
                        'key' => 'employees.attendance',
                        'title' => __('Attendance'),
                        'icon' => 'clock',
                        'route' => 'employees.attendance.index',
                        'permission' => 'employees.attendance.view',
                        'children' => []
                    ],
                    [
                        'key' => 'employees.payroll',
                        'title' => __('Payroll'),
                        'icon' => 'dollar-sign',
                        'route' => 'employees.payroll.index',
                        'permission' => 'employees.payroll.view',
                        'children' => []
                    ]
                ]
            ],
            [
                'key' => 'financial',
                'title' => __('Financial'),
                'icon' => 'trending-up',
                'route' => null,
                'permission' => 'financial.view',
                'children' => [
                    [
                        'key' => 'financial.cash',
                        'title' => __('Cash Management'),
                        'icon' => 'dollar-sign',
                        'route' => 'financial.cash.index',
                        'permission' => 'financial.cash.view',
                        'children' => []
                    ],
                    [
                        'key' => 'financial.payments',
                        'title' => __('Payments'),
                        'icon' => 'credit-card',
                        'route' => 'financial.payments.index',
                        'permission' => 'financial.payments.view',
                        'children' => []
                    ],
                    [
                        'key' => 'financial.reports',
                        'title' => __('Financial Reports'),
                        'icon' => 'bar-chart',
                        'route' => 'financial.reports.index',
                        'permission' => 'financial.reports.view',
                        'children' => []
                    ]
                ]
            ],
            [
                'key' => 'reports',
                'title' => __('Reports'),
                'icon' => 'bar-chart-2',
                'route' => null,
                'permission' => 'reports.view',
                'children' => [
                    [
                        'key' => 'reports.sales',
                        'title' => __('Sales Reports'),
                        'icon' => 'trending-up',
                        'route' => 'reports.sales.index',
                        'permission' => 'reports.sales.view',
                        'children' => []
                    ],
                    [
                        'key' => 'reports.inventory',
                        'title' => __('Inventory Reports'),
                        'icon' => 'archive',
                        'route' => 'reports.inventory.index',
                        'permission' => 'reports.inventory.view',
                        'children' => []
                    ],
                    [
                        'key' => 'reports.customers',
                        'title' => __('Customer Reports'),
                        'icon' => 'users',
                        'route' => 'reports.customers.index',
                        'permission' => 'reports.customers.view',
                        'children' => []
                    ]
                ]
            ]
        ];
    }
}