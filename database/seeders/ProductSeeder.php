<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Tạo dữ liệu mẫu cho products theo từng loại business
     */
    public function run(): void
    {
        $stores = DB::table('stores')->get();

        foreach ($stores as $store) {
            $storeSettings = json_decode($store->settings, true);
            $businessType = $storeSettings['business_type'] ?? 'retail';

            // Tạo categories theo business type
            $categories = $this->createCategoriesForStore($store->id, $businessType);

            // Tạo products theo business type
            $this->createProductsForStore($store->id, $businessType, $categories);

            $this->command->info("✅ Created products for {$store->name} ({$businessType})");
        }
    }

    private function createCategoriesForStore($storeId, $businessType)
    {
        $categories = [];

        if ($businessType === 'materials_management') {
            $categoryData = [
                ['name' => 'Xi măng & Chất kết dính', 'code' => 'CEMENT', 'sort_order' => 1],
                ['name' => 'Cát & Đá', 'code' => 'SAND_STONE', 'sort_order' => 2],
                ['name' => 'Gạch & Ngói', 'code' => 'BRICK_TILE', 'sort_order' => 3],
                ['name' => 'Thép & Kim loại', 'code' => 'STEEL_METAL', 'sort_order' => 4],
                ['name' => 'Gỗ & Vật liệu gỗ', 'code' => 'WOOD', 'sort_order' => 5],
                ['name' => 'Sơn & Hóa chất', 'code' => 'PAINT_CHEMICAL', 'sort_order' => 6],
                ['name' => 'Điện & Điện tử', 'code' => 'ELECTRICAL', 'sort_order' => 7],
                ['name' => 'Ống nước & Phụ kiện', 'code' => 'PLUMBING', 'sort_order' => 8],
            ];
        } elseif ($businessType === 'coffee_inventory') {
            $categoryData = [
                ['name' => 'Cà phê nhân xanh', 'code' => 'GREEN_BEANS', 'sort_order' => 1],
                ['name' => 'Cà phê rang', 'code' => 'ROASTED_BEANS', 'sort_order' => 2],
                ['name' => 'Cà phê xay', 'code' => 'GROUND_COFFEE', 'sort_order' => 3],
                ['name' => 'Cà phê hòa tan', 'code' => 'INSTANT_COFFEE', 'sort_order' => 4],
                ['name' => 'Blend & Specialty', 'code' => 'BLEND_SPECIALTY', 'sort_order' => 5],
                ['name' => 'Phụ liệu', 'code' => 'ACCESSORIES', 'sort_order' => 6],
                ['name' => 'Bao bì', 'code' => 'PACKAGING', 'sort_order' => 7],
                ['name' => 'Thiết bị', 'code' => 'EQUIPMENT', 'sort_order' => 8],
            ];
        } else { // fallback
            $categoryData = [
                ['name' => 'Sản phẩm chính', 'code' => 'MAIN_PRODUCTS', 'sort_order' => 1],
                ['name' => 'Phụ kiện', 'code' => 'ACCESSORIES', 'sort_order' => 2],
                ['name' => 'Dịch vụ', 'code' => 'SERVICES', 'sort_order' => 3],
            ];
        }

        foreach ($categoryData as $data) {
            $categoryId = DB::table('product_categories')->insertGetId([
                'store_id' => $storeId,
                'name' => $data['name'],
                'code' => $data['code'],
                'sort_order' => $data['sort_order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $categories[$data['code']] = $categoryId;
        }

        return $categories;
    }

    private function createProductsForStore($storeId, $businessType, $categories)
    {
        $products = [];

        if ($businessType === 'materials_management') {
            $products = [
                // Xi măng & Chất kết dính
                ['category' => 'CEMENT', 'name' => 'Xi măng Portland PCB40', 'code' => 'XM001', 'cost' => 95000, 'price' => 110000, 'unit' => 'bao'],
                ['category' => 'CEMENT', 'name' => 'Xi măng trắng', 'code' => 'XM002', 'cost' => 180000, 'price' => 200000, 'unit' => 'bao'],
                ['category' => 'CEMENT', 'name' => 'Keo dán gạch', 'code' => 'XM003', 'cost' => 45000, 'price' => 55000, 'unit' => 'bao'],

                // Cát & Đá
                ['category' => 'SAND_STONE', 'name' => 'Cát vàng xây dựng', 'code' => 'CD001', 'cost' => 350000, 'price' => 400000, 'unit' => 'm3'],
                ['category' => 'SAND_STONE', 'name' => 'Đá 1x2 (4x6)', 'code' => 'CD002', 'cost' => 420000, 'price' => 480000, 'unit' => 'm3'],
                ['category' => 'SAND_STONE', 'name' => 'Đá mi (sỏi)', 'code' => 'CD003', 'cost' => 380000, 'price' => 430000, 'unit' => 'm3'],

                // Gạch & Ngói
                ['category' => 'BRICK_TILE', 'name' => 'Gạch đỏ 220x105x60', 'code' => 'GN001', 'cost' => 1800, 'price' => 2200, 'unit' => 'viên'],
                ['category' => 'BRICK_TILE', 'name' => 'Gạch block 390x190x190', 'code' => 'GN002', 'cost' => 8500, 'price' => 10000, 'unit' => 'viên'],
                ['category' => 'BRICK_TILE', 'name' => 'Ngói lợp Đồng Nai', 'code' => 'GN003', 'cost' => 12000, 'price' => 15000, 'unit' => 'viên'],

                // Thép & Kim loại
                ['category' => 'STEEL_METAL', 'name' => 'Thép phi 10 Hòa Phát', 'code' => 'TK001', 'cost' => 18500, 'price' => 21000, 'unit' => 'kg'],
                ['category' => 'STEEL_METAL', 'name' => 'Thép phi 12 Hòa Phát', 'code' => 'TK002', 'cost' => 18200, 'price' => 20800, 'unit' => 'kg'],
                ['category' => 'STEEL_METAL', 'name' => 'Tôn lạnh 0.5mm', 'code' => 'TK003', 'cost' => 28000, 'price' => 32000, 'unit' => 'm2'],
            ];
        } elseif ($businessType === 'coffee_inventory') {
            $products = [
                // Cà phê nhân xanh
                ['category' => 'GREEN_BEANS', 'name' => 'Arabica Cầu Đất', 'code' => 'GB001', 'cost' => 85000, 'price' => 120000, 'unit' => 'kg'],
                ['category' => 'GREEN_BEANS', 'name' => 'Robusta Đắk Lắk', 'code' => 'GB002', 'cost' => 45000, 'price' => 65000, 'unit' => 'kg'],
                ['category' => 'GREEN_BEANS', 'name' => 'Arabica Ethiopia', 'code' => 'GB003', 'cost' => 180000, 'price' => 250000, 'unit' => 'kg'],
                ['category' => 'GREEN_BEANS', 'name' => 'Robusta Brazil', 'code' => 'GB004', 'cost' => 65000, 'price' => 90000, 'unit' => 'kg'],

                // Cà phê rang
                ['category' => 'ROASTED_BEANS', 'name' => 'Arabica rang vừa', 'code' => 'RB001', 'cost' => 120000, 'price' => 180000, 'unit' => 'kg'],
                ['category' => 'ROASTED_BEANS', 'name' => 'Robusta rang đậm', 'code' => 'RB002', 'cost' => 75000, 'price' => 110000, 'unit' => 'kg'],
                ['category' => 'ROASTED_BEANS', 'name' => 'Blend House Special', 'code' => 'RB003', 'cost' => 95000, 'price' => 140000, 'unit' => 'kg'],

                // Cà phê xay
                ['category' => 'GROUND_COFFEE', 'name' => 'Cà phê xay Espresso', 'code' => 'GC001', 'cost' => 140000, 'price' => 200000, 'unit' => 'kg'],
                ['category' => 'GROUND_COFFEE', 'name' => 'Cà phê xay Filter', 'code' => 'GC002', 'cost' => 120000, 'price' => 170000, 'unit' => 'kg'],

                // Phụ liệu
                ['category' => 'ACCESSORIES', 'name' => 'Sữa tươi Vinamilk', 'code' => 'ACC001', 'cost' => 28000, 'price' => 35000, 'unit' => 'lít'],
                ['category' => 'ACCESSORIES', 'name' => 'Đường trắng', 'code' => 'ACC002', 'cost' => 18000, 'price' => 25000, 'unit' => 'kg'],
                ['category' => 'ACCESSORIES', 'name' => 'Syrup Vanilla', 'code' => 'ACC003', 'cost' => 85000, 'price' => 120000, 'unit' => 'chai'],
            ];
        } else {
            $products = [
                ['category' => 'MAIN_PRODUCTS', 'name' => 'Sản phẩm mẫu', 'code' => 'SP001', 'cost' => 50000, 'price' => 75000, 'unit' => 'pcs'],
            ];
        }

        foreach ($products as $product) {
            $categoryId = $categories[$product['category']] ?? null;
            if (!$categoryId) continue;

            $productId = DB::table('products')->insertGetId([
                'store_id' => $storeId,
                'category_id' => $categoryId,
                'name' => $product['name'],
                'code' => $product['code'],
                'cost_price' => $product['cost'],
                'sale_price' => $product['price'],
                'wholesale_price' => $product['price'] * 0.9,
                'unit' => $product['unit'] ?? 'pcs',
                'min_stock' => $businessType === 'materials_management' ? 50 : ($businessType === 'coffee_inventory' ? 20 : 10),
                'max_stock' => $businessType === 'materials_management' ? 1000 : ($businessType === 'coffee_inventory' ? 500 : 100),
                'track_stock' => true,
                'tax_rate' => 10,
                'type' => 'product',
                'is_active' => true,
                'attributes' => json_encode($this->getProductAttributes($businessType, $product)),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Tạo stock record
            DB::table('stocks')->insert([
                'store_id' => $storeId,
                'product_id' => $productId,
                'quantity' => rand(50, 200),
                'reserved_quantity' => 0,
                'cost_price' => $product['cost'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function getProductAttributes($businessType, $product)
    {
        if ($businessType === 'materials_management') {
            return [
                'supplier' => 'Nhà cung cấp ABC',
                'warranty_months' => 12,
                'quality_grade' => 'A',
                'storage_requirements' => 'Nơi khô ráo, thoáng mát',
                'safety_notes' => 'Tuân thủ quy định an toàn lao động',
                'batch_tracking' => true,
                'project_applicable' => ['residential', 'commercial', 'industrial']
            ];
        } elseif ($businessType === 'coffee_inventory') {
            $attributes = [
                'origin' => $this->getCoffeeOrigin($product['code']),
                'roast_level' => $this->getRoastLevel($product['code']),
                'processing_method' => 'Washed',
                'harvest_season' => '2024',
                'altitude' => '1200-1500m',
                'cupping_score' => rand(80, 95),
                'moisture_content' => '12%',
                'storage_temp' => '15-20°C',
                'shelf_life_months' => 12
            ];

            if (str_contains($product['code'], 'GB')) {
                $attributes['bean_type'] = str_contains($product['name'], 'Arabica') ? 'Arabica' : 'Robusta';
                $attributes['screen_size'] = '16-18';
            }

            return $attributes;
        }

        return [];
    }

    private function getCoffeeOrigin($code)
    {
        $origins = [
            'GB001' => 'Cầu Đất, Đà Lạt',
            'GB002' => 'Đắk Lắk, Việt Nam',
            'GB003' => 'Yirgacheffe, Ethiopia',
            'GB004' => 'Cerrado, Brazil',
        ];

        return $origins[$code] ?? 'Việt Nam';
    }

    private function getRoastLevel($code)
    {
        if (str_contains($code, 'GB')) return 'Green (Chưa rang)';
        if (str_contains($code, 'RB001')) return 'Medium (Rang vừa)';
        if (str_contains($code, 'RB002')) return 'Dark (Rang đậm)';
        if (str_contains($code, 'RB003')) return 'Medium-Dark (Rang vừa đậm)';

        return 'Medium';
    }
}
