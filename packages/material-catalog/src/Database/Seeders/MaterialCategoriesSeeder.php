<?php

namespace Packages\MaterialCatalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\MaterialCatalog\Models\MaterialCategory;
use Packages\Store\Models\Store;

class MaterialCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = Store::active()->get();

        foreach ($stores as $store) {
            $this->createCategoriesForStore($store->id);
        }
    }

    /**
     * Create material categories for a specific store.
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
    }
}
