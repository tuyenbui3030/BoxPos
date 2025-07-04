<?php

namespace Packages\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Product\Models\Product;
use Packages\Product\Models\ProductCategory;
use Packages\Store\Models\Store;

class ProductSeeder extends Seeder
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

        // Get categories
        $coffeeCategory = ProductCategory::where('store_id', $store->id)
            ->where('code', 'CAT001001')
            ->first();

        $teaCategory = ProductCategory::where('store_id', $store->id)
            ->where('code', 'CAT001002')
            ->first();

        $cakeCategory = ProductCategory::where('store_id', $store->id)
            ->where('code', 'CAT002001')
            ->first();

        if (!$coffeeCategory || !$teaCategory || !$cakeCategory) {
            $this->command->warn('Categories not found. Please run ProductCategorySeeder first.');
            return;
        }

        $products = [
            // Coffee products
            [
                'category_id' => $coffeeCategory->id,
                'name' => 'Cà phê đen',
                'code' => 'PRD001',
                'barcode' => '8934567890123',
                'description' => 'Cà phê đen truyền thống',
                'unit' => 'ly',
                'cost_price' => 15000,
                'sale_price' => 25000,
                'min_stock' => 10,
                'max_stock' => 100,
            ],
            [
                'category_id' => $coffeeCategory->id,
                'name' => 'Cà phê sữa',
                'code' => 'PRD002',
                'barcode' => '8934567890124',
                'description' => 'Cà phê sữa đá',
                'unit' => 'ly',
                'cost_price' => 18000,
                'sale_price' => 30000,
                'min_stock' => 10,
                'max_stock' => 100,
            ],
            [
                'category_id' => $coffeeCategory->id,
                'name' => 'Cappuccino',
                'code' => 'PRD003',
                'barcode' => '8934567890125',
                'description' => 'Cappuccino Ý',
                'unit' => 'ly',
                'cost_price' => 25000,
                'sale_price' => 45000,
                'min_stock' => 5,
                'max_stock' => 50,
            ],
            // Tea products
            [
                'category_id' => $teaCategory->id,
                'name' => 'Trà đào',
                'code' => 'PRD004',
                'barcode' => '8934567890126',
                'description' => 'Trà đào cam sả',
                'unit' => 'ly',
                'cost_price' => 20000,
                'sale_price' => 35000,
                'min_stock' => 10,
                'max_stock' => 80,
            ],
            [
                'category_id' => $teaCategory->id,
                'name' => 'Trà sữa trân châu',
                'code' => 'PRD005',
                'barcode' => '8934567890127',
                'description' => 'Trà sữa trân châu đường đen',
                'unit' => 'ly',
                'cost_price' => 22000,
                'sale_price' => 40000,
                'min_stock' => 15,
                'max_stock' => 100,
            ],
            // Cake products
            [
                'category_id' => $cakeCategory->id,
                'name' => 'Bánh tiramisu',
                'code' => 'PRD006',
                'barcode' => '8934567890128',
                'description' => 'Bánh tiramisu Ý',
                'unit' => 'miếng',
                'cost_price' => 35000,
                'sale_price' => 65000,
                'min_stock' => 5,
                'max_stock' => 30,
            ],
            [
                'category_id' => $cakeCategory->id,
                'name' => 'Bánh cheesecake',
                'code' => 'PRD007',
                'barcode' => '8934567890129',
                'description' => 'Bánh cheesecake dâu',
                'unit' => 'miếng',
                'cost_price' => 30000,
                'sale_price' => 55000,
                'min_stock' => 5,
                'max_stock' => 25,
            ],
        ];

        foreach ($products as $productData) {
            Product::create([
                'store_id' => $store->id,
                'category_id' => $productData['category_id'],
                'name' => $productData['name'],
                'code' => $productData['code'],
                'barcode' => $productData['barcode'],
                'description' => $productData['description'],
                'unit' => $productData['unit'],
                'cost_price' => $productData['cost_price'],
                'sale_price' => $productData['sale_price'],
                'min_stock' => $productData['min_stock'],
                'max_stock' => $productData['max_stock'],
                'track_stock' => true,
                'is_active' => true,
            ]);
        }

        $this->command->info('Products seeded successfully!');
    }
}