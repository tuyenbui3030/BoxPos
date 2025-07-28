<?php

namespace Packages\MaterialCatalog\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\MaterialCatalog\Models\MaterialCategory;
use Packages\MaterialCatalog\Models\MaterialSpecification;
use Packages\MaterialCatalog\Models\MaterialUnit;
use Packages\Store\Models\Store;

class MaterialCatalogSeeder extends BasePackageSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensureSeedingAllowed();
        
        $this->executeWithTransaction(function () {
            $this->logSeedingProgress('material_catalog_seeding_started');

            // Seed standard material units first
            $this->seedMaterialUnits();
            
            // Seed material categories
            $this->seedMaterialCategories();
            
            // Seed building materials with specifications
            $this->seedBuildingMaterials();
            
            $this->logSeedingProgress('material_catalog_seeding_completed');
        });
    }

    /**
     * Seed material units for all stores
     */
    private function seedMaterialUnits(): void
    {
        $this->logSeedingProgress('seeding_material_units');
        
        $this->seedForAllStores(function (Store $store) {
            $this->createUnitsForStore($store->id);
        });
    }

    /**
     * Seed material categories for all stores
     */
    private function seedMaterialCategories(): void
    {
        $this->logSeedingProgress('seeding_material_categories');
        
        $this->seedForAllStores(function (Store $store) {
            $this->createCategoriesForStore($store->id);
        });
    }

    /**
     * Seed building materials with specifications using factories
     */
    private function seedBuildingMaterials(): void
    {
        $this->logSeedingProgress('seeding_building_materials');
        
        $this->seedForAllStores(function (Store $store) {
            $this->createMaterialsForStore($store);
        });
    }

    /**
     * Create material units for a specific store
     */
    private function createUnitsForStore(int $storeId): void
    {
        $units = [
            // Weight units
            [
                'code' => 'kg',
                'name' => 'Kilogram',
                'symbol' => 'kg',
                'type' => 'weight',
                'conversion_factor' => 1.0,
                'base_unit_code' => 'kg',
                'is_default' => true,
            ],
            [
                'code' => 'ton',
                'name' => 'Tấn',
                'symbol' => 'tấn',
                'type' => 'weight',
                'conversion_factor' => 1000.0,
                'base_unit_code' => 'kg',
                'is_default' => false,
            ],
            [
                'code' => 'quintal',
                'name' => 'Tạ',
                'symbol' => 'tạ',
                'type' => 'weight',
                'conversion_factor' => 100.0,
                'base_unit_code' => 'kg',
                'is_default' => false,
            ],

            // Volume units
            [
                'code' => 'm3',
                'name' => 'Mét khối',
                'symbol' => 'm³',
                'type' => 'volume',
                'conversion_factor' => 1.0,
                'base_unit_code' => 'm3',
                'is_default' => true,
            ],
            [
                'code' => 'liter',
                'name' => 'Lít',
                'symbol' => 'l',
                'type' => 'volume',
                'conversion_factor' => 0.001,
                'base_unit_code' => 'm3',
                'is_default' => false,
            ],

            // Area units
            [
                'code' => 'm2',
                'name' => 'Mét vuông',
                'symbol' => 'm²',
                'type' => 'area',
                'conversion_factor' => 1.0,
                'base_unit_code' => 'm2',
                'is_default' => true,
            ],

            // Length units
            [
                'code' => 'm',
                'name' => 'Mét',
                'symbol' => 'm',
                'type' => 'length',
                'conversion_factor' => 1.0,
                'base_unit_code' => 'm',
                'is_default' => true,
            ],
            [
                'code' => 'cm',
                'name' => 'Centimet',
                'symbol' => 'cm',
                'type' => 'length',
                'conversion_factor' => 0.01,
                'base_unit_code' => 'm',
                'is_default' => false,
            ],
            [
                'code' => 'mm',
                'name' => 'Millimet',
                'symbol' => 'mm',
                'type' => 'length',
                'conversion_factor' => 0.001,
                'base_unit_code' => 'm',
                'is_default' => false,
            ],

            // Count units
            [
                'code' => 'piece',
                'name' => 'Cái',
                'symbol' => 'cái',
                'type' => 'count',
                'conversion_factor' => 1.0,
                'base_unit_code' => 'piece',
                'is_default' => true,
            ],
            [
                'code' => 'bag',
                'name' => 'Bao',
                'symbol' => 'bao',
                'type' => 'count',
                'conversion_factor' => 1.0,
                'base_unit_code' => 'piece',
                'is_default' => false,
            ],
            [
                'code' => 'box',
                'name' => 'Thùng',
                'symbol' => 'thùng',
                'type' => 'count',
                'conversion_factor' => 1.0,
                'base_unit_code' => 'piece',
                'is_default' => false,
            ],
            [
                'code' => 'bundle',
                'name' => 'Bó',
                'symbol' => 'bó',
                'type' => 'count',
                'conversion_factor' => 1.0,
                'base_unit_code' => 'piece',
                'is_default' => false,
            ],
            [
                'code' => 'roll',
                'name' => 'Cuộn',
                'symbol' => 'cuộn',
                'type' => 'count',
                'conversion_factor' => 1.0,
                'base_unit_code' => 'piece',
                'is_default' => false,
            ],
            [
                'code' => 'sheet',
                'name' => 'Tấm',
                'symbol' => 'tấm',
                'type' => 'count',
                'conversion_factor' => 1.0,
                'base_unit_code' => 'piece',
                'is_default' => false,
            ],
            [
                'code' => 'bar',
                'name' => 'Thanh',
                'symbol' => 'thanh',
                'type' => 'count',
                'conversion_factor' => 1.0,
                'base_unit_code' => 'piece',
                'is_default' => false,
            ],
        ];

        foreach ($units as $unitData) {
            MaterialUnit::create(array_merge($unitData, [
                'store_id' => $storeId,
                'is_active' => true,
                'description' => "Đơn vị {$unitData['name']} cho vật liệu xây dựng",
            ]));
        }

        $this->logSeedingProgress('created_material_units_for_store', [
            'store_id' => $storeId,
            'units_count' => count($units)
        ]);
    }

    /**
     * Create material categories for a specific store
     */
    private function createCategoriesForStore(int $storeId): void
    {
        $categories = [
            [
                'code' => 'cement',
                'name' => 'Xi măng',
                'description' => 'Các loại xi măng xây dựng',
                'icon' => 'fas fa-cube',
                'color' => '#8B4513',
                'sort_order' => 1,
                'subcategories' => [
                    ['code' => 'cement_portland', 'name' => 'Xi măng Portland'],
                    ['code' => 'cement_composite', 'name' => 'Xi măng hỗn hợp'],
                    ['code' => 'cement_white', 'name' => 'Xi măng trắng'],
                ]
            ],
            [
                'code' => 'steel',
                'name' => 'Sắt thép',
                'description' => 'Vật liệu sắt thép xây dựng',
                'icon' => 'fas fa-industry',
                'color' => '#708090',
                'sort_order' => 2,
                'subcategories' => [
                    ['code' => 'rebar', 'name' => 'Thép cây'],
                    ['code' => 'steel_sheet', 'name' => 'Tôn thép'],
                    ['code' => 'steel_pipe', 'name' => 'Ống thép'],
                    ['code' => 'steel_angle', 'name' => 'Thép góc'],
                ]
            ],
            [
                'code' => 'brick',
                'name' => 'Gạch',
                'description' => 'Các loại gạch xây dựng',
                'icon' => 'fas fa-th-large',
                'color' => '#CD853F',
                'sort_order' => 3,
                'subcategories' => [
                    ['code' => 'red_brick', 'name' => 'Gạch đỏ'],
                    ['code' => 'concrete_brick', 'name' => 'Gạch bê tông'],
                    ['code' => 'fire_brick', 'name' => 'Gạch chịu lửa'],
                    ['code' => 'hollow_brick', 'name' => 'Gạch rỗng'],
                ]
            ],
            [
                'code' => 'sand_stone',
                'name' => 'Cát đá',
                'description' => 'Cát, đá và vật liệu san lấp',
                'icon' => 'fas fa-mountain',
                'color' => '#D2B48C',
                'sort_order' => 4,
                'subcategories' => [
                    ['code' => 'sand', 'name' => 'Cát'],
                    ['code' => 'gravel', 'name' => 'Đá dăm'],
                    ['code' => 'crushed_stone', 'name' => 'Đá nghiền'],
                    ['code' => 'river_stone', 'name' => 'Đá sông'],
                ]
            ],
            [
                'code' => 'tile',
                'name' => 'Ngói',
                'description' => 'Ngói lợp mái',
                'icon' => 'fas fa-home',
                'color' => '#B22222',
                'sort_order' => 5,
                'subcategories' => [
                    ['code' => 'clay_tile', 'name' => 'Ngói đất nung'],
                    ['code' => 'concrete_tile', 'name' => 'Ngói bê tông'],
                    ['code' => 'metal_tile', 'name' => 'Ngói kim loại'],
                ]
            ],
            [
                'code' => 'paint',
                'name' => 'Sơn',
                'description' => 'Sơn và vật liệu hoàn thiện',
                'icon' => 'fas fa-paint-brush',
                'color' => '#FF6347',
                'sort_order' => 6,
                'subcategories' => [
                    ['code' => 'interior_paint', 'name' => 'Sơn nội thất'],
                    ['code' => 'exterior_paint', 'name' => 'Sơn ngoại thất'],
                    ['code' => 'primer', 'name' => 'Sơn lót'],
                    ['code' => 'wood_stain', 'name' => 'Sơn gỗ'],
                ]
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = MaterialCategory::create([
                'store_id' => $storeId,
                'code' => $categoryData['code'],
                'name' => $categoryData['name'],
                'slug' => \Str::slug($categoryData['name']),
                'description' => $categoryData['description'],
                'icon' => $categoryData['icon'],
                'color' => $categoryData['color'],
                'sort_order' => $categoryData['sort_order'],
                'is_active' => true,
                'show_in_menu' => true,
            ]);

            // Create subcategories
            if (isset($categoryData['subcategories'])) {
                foreach ($categoryData['subcategories'] as $index => $subcat) {
                    MaterialCategory::create([
                        'store_id' => $storeId,
                        'parent_id' => $category->id,
                        'code' => $subcat['code'],
                        'name' => $subcat['name'],
                        'slug' => \Str::slug($subcat['name']),
                        'description' => "Danh mục con của {$category->name}",
                        'sort_order' => $index + 1,
                        'is_active' => true,
                        'show_in_menu' => true,
                    ]);
                }
            }
        }

        $this->logSeedingProgress('created_material_categories_for_store', [
            'store_id' => $storeId,
            'categories_count' => count($categories)
        ]);
    }

    /**
     * Create building materials with specifications for a specific store
     */
    private function createMaterialsForStore(Store $store): void
    {
        $categories = MaterialCategory::where('store_id', $store->id)->get();
        $units = MaterialUnit::where('store_id', $store->id)->get();
        
        if ($categories->isEmpty() || $units->isEmpty()) {
            $this->logSeedingProgress('skipping_materials_creation', [
                'store_id' => $store->id,
                'reason' => 'No categories or units found'
            ]);
            return;
        }

        $materialsCount = $this->getRecordCount(50, 10);
        
        // Get a user from this store for created_by
        $storeUser = $store->users()->first();
        $createdBy = $storeUser ? $storeUser->id : null;

        // Create materials using factory
        $materials = BuildingMaterial::factory()
            ->count($materialsCount)
            ->create([
                'store_id' => $store->id,
                'category_id' => fn() => $categories->random()->id,
                'primary_unit_id' => fn() => $units->random()->id,
                'created_by' => $createdBy,
            ]);

        // Create specifications for each material
        foreach ($materials as $material) {
            $specsCount = $this->getRecordCount(5, 2);
            
            MaterialSpecification::factory()
                ->count($specsCount)
                ->forMaterial($material)
                ->create();
        }

        $this->logSeedingProgress('created_building_materials_for_store', [
            'store_id' => $store->id,
            'materials_count' => $materialsCount,
            'specifications_count' => $materialsCount * $specsCount
        ]);
    }
}
