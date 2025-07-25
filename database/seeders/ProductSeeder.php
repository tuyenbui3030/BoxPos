<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Products\Models\ProductCategory;
use Packages\Products\Models\Product;
use Packages\Products\Models\ProductVariant;
use Packages\Products\Models\Service;
use Packages\Store\Models\Store;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::all();

        foreach ($stores as $store) {
            // Create product categories
            $categories = $this->createProductCategories($store);
            
            // Create products
            $products = $this->createProducts($store, $categories);
            
            // Create product variants
            $this->createProductVariants($products);
            
            // Services will be created by ServiceSeeder
        }
    }

    private function createProductCategories($store)
    {
        $categories = [];
        
        $categoryData = [
            ['code' => 'TOOLS', 'name' => 'Dụng cụ xây dựng', 'description' => 'Các loại dụng cụ, công cụ xây dựng'],
            ['code' => 'HARDWARE', 'name' => 'Phụ kiện kim khí', 'description' => 'Ốc vít, đinh, móc treo...'],
            ['code' => 'PAINT', 'name' => 'Sơn và chất hoàn thiện', 'description' => 'Sơn nước, sơn dầu, chất chống thấm'],
            ['code' => 'ELECTRICAL', 'name' => 'Thiết bị điện', 'description' => 'Dây điện, ổ cắm, công tắc'],
            ['code' => 'PLUMBING', 'name' => 'Thiết bị nước', 'description' => 'Ống nước, van, khớp nối'],
        ];

        foreach ($categoryData as $index => $data) {
            $category = ProductCategory::create(array_merge($data, [
                'store_id' => $store->id,
                'sort_order' => $index + 1,
                'is_active' => true,
                'color' => '#' . substr(md5($data['code']), 0, 6),
            ]));
            $categories[$data['code']] = $category;
        }

        return $categories;
    }

    private function createProducts($store, $categories)
    {
        $products = [];
        
        $productData = [
            [
                'category' => 'TOOLS',
                'sku' => 'TOOL001',
                'name' => 'Búa cán gỗ 500g',
                'description' => 'Búa cán gỗ trọng lượng 500g, chất lượng cao',
                'cost_price' => 150000,
                'selling_price' => 200000,
                'stock_quantity' => 50,
                'min_stock_level' => 10,
                'weight' => 0.5,
                'unit' => 'cái',
            ],
            [
                'category' => 'TOOLS',
                'sku' => 'TOOL002',
                'name' => 'Thước dây 5m',
                'description' => 'Thước dây cuộn 5 mét, vỏ nhựa chống sốc',
                'cost_price' => 80000,
                'selling_price' => 120000,
                'stock_quantity' => 30,
                'min_stock_level' => 5,
                'weight' => 0.3,
                'unit' => 'cái',
            ],
            [
                'category' => 'HARDWARE',
                'sku' => 'HW001',
                'name' => 'Ốc vít tôn 4.2x19mm',
                'description' => 'Ốc vít tôn tự khoan 4.2x19mm, mạ kẽm',
                'cost_price' => 500,
                'selling_price' => 800,
                'stock_quantity' => 1000,
                'min_stock_level' => 100,
                'weight' => 0.005,
                'unit' => 'cái',
            ],
            [
                'category' => 'PAINT',
                'sku' => 'PAINT001',
                'name' => 'Sơn nước nội thất 5L',
                'description' => 'Sơn nước nội thất cao cấp, thùng 5 lít',
                'cost_price' => 350000,
                'selling_price' => 450000,
                'stock_quantity' => 25,
                'min_stock_level' => 5,
                'weight' => 5.5,
                'unit' => 'thùng',
            ],
            [
                'category' => 'ELECTRICAL',
                'sku' => 'ELEC001',
                'name' => 'Dây điện đơn 2.5mm²',
                'description' => 'Dây điện đơn 2.5mm², cuộn 100m',
                'cost_price' => 800000,
                'selling_price' => 1000000,
                'stock_quantity' => 15,
                'min_stock_level' => 3,
                'weight' => 8.0,
                'unit' => 'cuộn',
            ],
        ];

        foreach ($productData as $data) {
            $product = Product::create([
                'store_id' => $store->id,
                'category_id' => $categories[$data['category']]->id,
                'sku' => $data['sku'],
                'name' => $data['name'],
                'description' => $data['description'],
                'cost_price' => $data['cost_price'],
                'selling_price' => $data['selling_price'],
                'stock_quantity' => $data['stock_quantity'],
                'available_quantity' => $data['stock_quantity'],
                'min_stock_level' => $data['min_stock_level'],
                'reorder_point' => $data['min_stock_level'] * 2,
                'reorder_quantity' => $data['stock_quantity'],
                'weight' => $data['weight'],
                'unit' => $data['unit'],
                'status' => 'active',
                'type' => 'simple',
                'track_inventory' => true,
                'is_taxable' => true,
                'tax_rate' => 10,
                'currency' => 'VND',
                'tags' => ['xây dựng', 'công cụ'],
                'metadata' => [
                    'warranty' => '12 tháng',
                    'origin' => 'Việt Nam',
                ],
            ]);
            $products[] = $product;
        }

        return $products;
    }

    private function createProductVariants($products)
    {
        // Create variants for some products
        $variantProduct = $products[0]; // Búa cán gỗ
        
        $variants = [
            [
                'name' => 'Búa cán gỗ 300g',
                'attributes' => ['weight' => '300g'],
                'attribute_summary' => '300g',
                'cost_price' => 120000,
                'selling_price' => 160000,
                'stock_quantity' => 30,
                'weight' => 0.3,
            ],
            [
                'name' => 'Búa cán gỗ 800g',
                'attributes' => ['weight' => '800g'],
                'attribute_summary' => '800g',
                'cost_price' => 180000,
                'selling_price' => 240000,
                'stock_quantity' => 20,
                'weight' => 0.8,
                'is_default' => true,
            ],
        ];

        foreach ($variants as $index => $variantData) {
            ProductVariant::create(array_merge($variantData, [
                'product_id' => $variantProduct->id,
                'sku' => $variantProduct->sku . '-V' . ($index + 1) . '-' . time() . rand(100, 999),
                'available_quantity' => $variantData['stock_quantity'],
                'status' => 'active',
                'attributes' => json_encode($variantData['attributes']),
            ]));
        }

        // Update product type to variable
        $variantProduct->update(['type' => 'variable']);
    }

    private function createServices($store, $categories)
    {
        $serviceData = [
            [
                'category' => 'TOOLS',
                'code' => 'SRV001',
                'name' => 'Dịch vụ lắp đặt',
                'description' => 'Dịch vụ lắp đặt thiết bị, công cụ xây dựng',
                'base_price' => 500000,
                'hourly_rate' => 200000,
                'billing_type' => 'hourly',
                'estimated_duration' => 120, // 2 hours
                'delivery_method' => 'on_site',
            ],
            [
                'category' => 'ELECTRICAL',
                'code' => 'SRV002',
                'name' => 'Dịch vụ sửa chữa điện',
                'description' => 'Dịch vụ sửa chữa, bảo trì hệ thống điện',
                'base_price' => 300000,
                'hourly_rate' => 150000,
                'billing_type' => 'hourly',
                'estimated_duration' => 180, // 3 hours
                'delivery_method' => 'on_site',
                'requires_booking' => true,
                'booking_lead_time' => 24,
            ],
            [
                'category' => 'PAINT',
                'code' => 'SRV003',
                'name' => 'Dịch vụ tư vấn màu sơn',
                'description' => 'Tư vấn chọn màu sơn phù hợp cho không gian',
                'base_price' => 200000,
                'billing_type' => 'fixed',
                'estimated_duration' => 60, // 1 hour
                'delivery_method' => 'in_person',
                'requires_booking' => true,
                'booking_lead_time' => 12,
            ],
        ];

        foreach ($serviceData as $data) {
            Service::create(array_merge($data, [
                'store_id' => $store->id,
                'category_id' => $categories[$data['category']]->id,
                'status' => 'active',
                'type' => 'standard',
                'currency' => 'VND',
                'is_taxable' => true,
                'tax_rate' => 10,
                'requires_staff' => true,
                'min_staff' => 1,
                'is_featured' => rand(0, 1) == 1,
                'allow_online_booking' => true,
                'send_confirmation' => true,
                'send_reminder' => true,
                'reminder_hours' => 24,
                'tags' => ['dịch vụ', 'xây dựng'],
            ]));
        }
    }
}
