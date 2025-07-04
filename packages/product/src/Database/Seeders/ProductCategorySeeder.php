<?php

namespace Packages\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Product\Models\ProductCategory;
use Packages\Store\Models\Store;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first store for demo data
        $store = Store::first();

        if (!$store) {
            $this->command->warn('No stores found. Please create a store first.');
            return;
        }

        $categories = [
            [
                'name' => 'Đồ uống',
                'code' => 'CAT001',
                'description' => 'Các loại đồ uống',
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Cà phê', 'code' => 'CAT001001', 'sort_order' => 1],
                    ['name' => 'Trà', 'code' => 'CAT001002', 'sort_order' => 2],
                    ['name' => 'Nước ngọt', 'code' => 'CAT001003', 'sort_order' => 3],
                    ['name' => 'Nước ép', 'code' => 'CAT001004', 'sort_order' => 4],
                ]
            ],
            [
                'name' => 'Đồ ăn',
                'code' => 'CAT002',
                'description' => 'Các loại đồ ăn',
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Bánh ngọt', 'code' => 'CAT002001', 'sort_order' => 1],
                    ['name' => 'Bánh mì', 'code' => 'CAT002002', 'sort_order' => 2],
                    ['name' => 'Snack', 'code' => 'CAT002003', 'sort_order' => 3],
                ]
            ],
            [
                'name' => 'Phụ kiện',
                'code' => 'CAT003',
                'description' => 'Các loại phụ kiện',
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Túi xách', 'code' => 'CAT003001', 'sort_order' => 1],
                    ['name' => 'Ly cốc', 'code' => 'CAT003002', 'sort_order' => 2],
                ]
            ]
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $category = ProductCategory::create([
                'store_id' => $store->id,
                'name' => $categoryData['name'],
                'code' => $categoryData['code'],
                'description' => $categoryData['description'] ?? null,
                'sort_order' => $categoryData['sort_order'],
                'is_active' => true,
            ]);

            // Create child categories
            foreach ($children as $childData) {
                ProductCategory::create([
                    'store_id' => $store->id,
                    'parent_id' => $category->id,
                    'name' => $childData['name'],
                    'code' => $childData['code'],
                    'sort_order' => $childData['sort_order'],
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Product categories seeded successfully!');
    }
}