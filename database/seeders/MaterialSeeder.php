<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Packages\MaterialCatalog\Models\MaterialCategory;
use Packages\MaterialCatalog\Models\MaterialUnit;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\MaterialSuppliers\Models\MaterialSupplier;
use Packages\MaterialInventory\Models\MaterialInventory;
use Packages\Store\Models\Store;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::all();

        foreach ($stores as $store) {
            // Create material units
            $units = $this->createUnits($store);

            // Create material categories
            $categories = $this->createCategories($store);

            // Create suppliers
            $suppliers = $this->createSuppliers($store);

            // Create materials
            $materials = $this->createMaterials($store, $categories, $suppliers, $units);

            // Create inventory records
            $this->createInventory($store, $materials);
        }
    }

    private function createUnits($store)
    {
        $units = [];

        $unitData = [
            ['code' => 'bao', 'name' => 'Bao', 'symbol' => 'bao', 'type' => 'count'],
            ['code' => 'cay', 'name' => 'Cây', 'symbol' => 'cây', 'type' => 'count'],
            ['code' => 'vien', 'name' => 'Viên', 'symbol' => 'viên', 'type' => 'count'],
            ['code' => 'kg', 'name' => 'Kilogram', 'symbol' => 'kg', 'type' => 'weight'],
            ['code' => 'tan', 'name' => 'Tấn', 'symbol' => 'tấn', 'type' => 'weight'],
            ['code' => 'm3', 'name' => 'Mét khối', 'symbol' => 'm³', 'type' => 'volume'],
            ['code' => 'm2', 'name' => 'Mét vuông', 'symbol' => 'm²', 'type' => 'area'],
            ['code' => 'm', 'name' => 'Mét', 'symbol' => 'm', 'type' => 'length'],
        ];

        foreach ($unitData as $data) {
            $unit = MaterialUnit::create(array_merge($data, [
                'store_id' => $store->id,
                'conversion_factor' => 1.0,
                'is_active' => true,
                'is_default' => $data['code'] === 'kg', // kg is default for weight
            ]));
            $units[$data['code']] = $unit;
        }

        return $units;
    }

    private function createCategories($store)
    {
        $categories = [];
        
        // Main categories
        $mainCategories = [
            ['code' => 'CEMENT', 'name' => 'Xi măng', 'description' => 'Các loại xi măng xây dựng'],
            ['code' => 'STEEL', 'name' => 'Thép xây dựng', 'description' => 'Thép các loại'],
            ['code' => 'BRICK', 'name' => 'Gạch xây dựng', 'description' => 'Gạch các loại'],
            ['code' => 'SAND', 'name' => 'Cát xây dựng', 'description' => 'Cát các loại'],
            ['code' => 'STONE', 'name' => 'Đá xây dựng', 'description' => 'Đá các loại'],
        ];

        foreach ($mainCategories as $index => $catData) {
            $category = MaterialCategory::create(array_merge($catData, [
                'store_id' => $store->id,
                'slug' => Str::slug($catData['name']),
                'sort_order' => $index + 1,
                'is_active' => true,
            ]));
            $categories[$catData['code']] = $category;
        }

        // Sub categories
        $subCategories = [
            ['parent' => 'CEMENT', 'code' => 'CEMENT_PC30', 'name' => 'Xi măng PC30'],
            ['parent' => 'CEMENT', 'code' => 'CEMENT_PC40', 'name' => 'Xi măng PC40'],
            ['parent' => 'STEEL', 'code' => 'STEEL_REBAR', 'name' => 'Thép cây'],
            ['parent' => 'STEEL', 'code' => 'STEEL_SHEET', 'name' => 'Thép tấm'],
            ['parent' => 'BRICK', 'code' => 'BRICK_RED', 'name' => 'Gạch đỏ'],
            ['parent' => 'BRICK', 'code' => 'BRICK_BLOCK', 'name' => 'Gạch block'],
        ];

        foreach ($subCategories as $index => $subCat) {
            $category = MaterialCategory::create([
                'store_id' => $store->id,
                'parent_id' => $categories[$subCat['parent']]->id,
                'code' => $subCat['code'],
                'name' => $subCat['name'],
                'slug' => Str::slug($subCat['name']),
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
            $categories[$subCat['code']] = $category;
        }

        return $categories;
    }

    private function createSuppliers($store)
    {
        $suppliers = [];
        
        $supplierData = [
            [
                'supplier_code' => 'SUP001',
                'company_name' => 'Công ty TNHH Vật liệu Xây dựng Hoàng Long',
                'contact_person' => 'Nguyễn Văn Hoàng',
                'phone' => '0281234567',
                'email' => 'hoanglong@gmail.com',
                'address' => '123 Quốc lộ 1A, Bình Chánh, TP.HCM',
                'tax_code' => '0123456789',
                'payment_terms' => 'net_30',
                'credit_limit' => 500000000,
                'is_active' => true,
                'supplier_type' => 'distributor',
            ],
            [
                'supplier_code' => 'SUP002',
                'company_name' => 'Công ty Cổ phần Xi măng Hà Tiên',
                'contact_person' => 'Trần Thị Mai',
                'phone' => '0287654321',
                'email' => 'hatien@cement.vn',
                'address' => '456 Đường Hà Tiên, Kiên Giang',
                'tax_code' => '0987654321',
                'payment_terms' => 'cash',
                'credit_limit' => 1000000000,
                'is_active' => true,
                'supplier_type' => 'manufacturer',
            ],
            [
                'supplier_code' => 'SUP003',
                'company_name' => 'Công ty TNHH Thép Hòa Phát',
                'contact_person' => 'Lê Văn Phát',
                'phone' => '0289876543',
                'email' => 'hoaphat@steel.vn',
                'address' => '789 Khu công nghiệp, Đồng Nai',
                'tax_code' => '0456789123',
                'payment_terms' => 'net_15',
                'credit_limit' => 2000000000,
                'is_active' => true,
                'supplier_type' => 'manufacturer',
            ],
        ];

        foreach ($supplierData as $data) {
            $supplier = MaterialSupplier::create(array_merge($data, [
                'store_id' => $store->id,
            ]));
            $suppliers[] = $supplier;
        }

        return $suppliers;
    }

    private function createMaterials($store, $categories, $suppliers, $units)
    {
        $materials = [];
        
        $materialData = [
            [
                'category' => 'CEMENT_PC30',
                'material_code' => 'XM_PC30_50KG',
                'name' => 'Xi măng PC30 bao 50kg',
                'description' => 'Xi măng Portland PC30 bao 50kg chất lượng cao',
                'unit' => 'bao',
                'weight' => 50,
                'brand' => 'Hà Tiên',
                'specifications' => ['strength' => '30 MPa', 'setting_time' => '45 phút'],
            ],
            [
                'category' => 'CEMENT_PC40',
                'material_code' => 'XM_PC40_50KG',
                'name' => 'Xi măng PC40 bao 50kg',
                'description' => 'Xi măng Portland PC40 bao 50kg cường độ cao',
                'unit' => 'bao',
                'weight' => 50,
                'brand' => 'Hà Tiên',
                'specifications' => ['strength' => '40 MPa', 'setting_time' => '30 phút'],
            ],
            [
                'category' => 'STEEL_REBAR',
                'material_code' => 'THEP_CAY_D10',
                'name' => 'Thép cây D10',
                'description' => 'Thép cây đường kính 10mm dài 12m',
                'unit' => 'cay',
                'weight' => 7.4,
                'brand' => 'Hòa Phát',
                'specifications' => ['diameter' => '10mm', 'length' => '12m', 'grade' => 'CB300V'],
            ],
            [
                'category' => 'STEEL_REBAR',
                'material_code' => 'THEP_CAY_D12',
                'name' => 'Thép cây D12',
                'description' => 'Thép cây đường kính 12mm dài 12m',
                'unit' => 'cay',
                'weight' => 10.7,
                'brand' => 'Hòa Phát',
                'specifications' => ['diameter' => '12mm', 'length' => '12m', 'grade' => 'CB300V'],
            ],
            [
                'category' => 'BRICK_RED',
                'material_code' => 'GACH_DO_220',
                'name' => 'Gạch đỏ 220x105x60',
                'description' => 'Gạch đỏ nung kích thước 220x105x60mm',
                'unit' => 'vien',
                'weight' => 2.5,
                'brand' => 'Đồng Tâm',
                'specifications' => ['size' => '220x105x60mm', 'strength' => 'M100'],
            ],
        ];

        foreach ($materialData as $index => $data) {
            $material = BuildingMaterial::create([
                'store_id' => $store->id,
                'category_id' => $categories[$data['category']]->id,
                'primary_unit_id' => $units[$data['unit']]->id,
                'material_code' => $data['material_code'],
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'],
                'brand' => $data['brand'],
                'weight_per_unit' => $data['weight'],
                'technical_specs' => $data['specifications'],
                'is_active' => true,
                'is_featured' => false,
                'track_serial_numbers' => false,
                'track_batch_numbers' => true,
            ]);
            $materials[] = $material;
        }

        return $materials;
    }

    private function createInventory($store, $materials)
    {
        foreach ($materials as $material) {
            $onHand = rand(100, 1000);
            $reserved = rand(0, 50);
            $available = max(0, $onHand - $reserved);
            $unitCost = rand(50000, 500000);

            MaterialInventory::create([
                'store_id' => $store->id,
                'material_id' => $material->id,
                'location_code' => 'MAIN',
                'location_name' => 'Kho chính',
                'zone' => 'A',
                'aisle' => 'A01',
                'shelf' => '01',
                'bin' => '001',
                'quantity_on_hand' => $onHand,
                'quantity_reserved' => $reserved,
                'quantity_available' => $available,
                'quantity_incoming' => 0,
                'quantity_outgoing' => 0,
                'batch_number' => 'BATCH' . date('Ymd') . rand(1000, 9999),
                'manufacture_date' => now()->subDays(rand(30, 180)),
                'expiry_date' => now()->addMonths(12),
                'received_date' => now()->subDays(rand(1, 30)),
                'unit_cost' => $unitCost,
                'total_cost' => $onHand * $unitCost,
                'cost_method' => 'fifo',
                'condition' => 'new',
                'quality_checked' => true,
                'last_quality_check' => now()->subDays(rand(1, 7)),
                'min_stock_level' => rand(10, 50),
                'max_stock_level' => rand(500, 1000),
                'last_movement_at' => now()->subDays(rand(1, 7)),
                'movement_count' => rand(5, 50),
                'is_active' => true,
                'notes' => 'Dữ liệu mẫu từ seeder',
            ]);
        }
    }
}
