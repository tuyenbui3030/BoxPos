<?php

namespace Packages\MaterialCatalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\MaterialCatalog\Models\MaterialCategory;
use Packages\MaterialCatalog\Models\MaterialUnit;
use Packages\Store\Models\Store;

class BuildingMaterialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = Store::active()->get();

        foreach ($stores as $store) {
            $this->createMaterialsForStore($store->id);
        }
    }

    /**
     * Create building materials for a specific store.
     */
    private function createMaterialsForStore(int $storeId): void
    {
        // Get categories and units for this store
        $cementCategory = MaterialCategory::where('store_id', $storeId)->where('code', 'cement_portland')->first();
        $steelCategory = MaterialCategory::where('store_id', $storeId)->where('code', 'rebar')->first();
        $brickCategory = MaterialCategory::where('store_id', $storeId)->where('code', 'red_brick')->first();
        $sandCategory = MaterialCategory::where('store_id', $storeId)->where('code', 'sand')->first();
        $paintCategory = MaterialCategory::where('store_id', $storeId)->where('code', 'interior_paint')->first();

        $bagUnit = MaterialUnit::where('store_id', $storeId)->where('code', 'bag')->first();
        $tonUnit = MaterialUnit::where('store_id', $storeId)->where('code', 'ton')->first();
        $pieceUnit = MaterialUnit::where('store_id', $storeId)->where('code', 'piece')->first();
        $m3Unit = MaterialUnit::where('store_id', $storeId)->where('code', 'm3')->first();
        $literUnit = MaterialUnit::where('store_id', $storeId)->where('code', 'liter')->first();

        $materials = [
            // Xi măng
            [
                'category_id' => $cementCategory?->id,
                'primary_unit_id' => $bagUnit?->id,
                'material_code' => 'MAT000001',
                'name' => 'Xi măng Portland PCB40 Hà Tiên',
                'description' => 'Xi măng Portland hỗn hợp PCB40 của Hà Tiên, phù hợp cho các công trình dân dụng và công nghiệp',
                'brand' => 'Hà Tiên',
                'model' => 'PCB40',
                'origin_country' => 'Vietnam',
                'weight_per_unit' => 50,
                'quality_standards' => ['TCVN 2682:2020'],
                'technical_specs' => [
                    'strength' => '40 MPa',
                    'setting_time' => '45-375 phút',
                    'fineness' => '≥ 2800 cm²/g'
                ],
                'is_featured' => true,
            ],
            [
                'category_id' => $cementCategory?->id,
                'primary_unit_id' => $bagUnit?->id,
                'material_code' => 'MAT000002',
                'name' => 'Xi măng Portland PCB50 Hà Tiên',
                'description' => 'Xi măng Portland PCB50 cường độ cao cho công trình đặc biệt',
                'brand' => 'Hà Tiên',
                'model' => 'PCB50',
                'origin_country' => 'Vietnam',
                'weight_per_unit' => 50,
                'quality_standards' => ['TCVN 2682:2020'],
                'technical_specs' => [
                    'strength' => '50 MPa',
                    'setting_time' => '45-375 phút',
                    'fineness' => '≥ 3000 cm²/g'
                ],
                'is_featured' => false,
            ],

            // Thép
            [
                'category_id' => $steelCategory?->id,
                'primary_unit_id' => $tonUnit?->id,
                'material_code' => 'MAT000003',
                'name' => 'Thép cây CB240-T Hòa Phát D10',
                'description' => 'Thép cây tròn trơn CB240-T đường kính 10mm của Hòa Phát',
                'brand' => 'Hòa Phát',
                'model' => 'CB240-T',
                'origin_country' => 'Vietnam',
                'weight_per_unit' => 1000,
                'quality_standards' => ['TCVN 1651:2018'],
                'technical_specs' => [
                    'diameter' => '10mm',
                    'yield_strength' => '240 MPa',
                    'tensile_strength' => '350-500 MPa'
                ],
                'is_featured' => true,
            ],
            [
                'category_id' => $steelCategory?->id,
                'primary_unit_id' => $tonUnit?->id,
                'material_code' => 'MAT000004',
                'name' => 'Thép cây CB300-V Hòa Phát D12',
                'description' => 'Thép cây có gân CB300-V đường kính 12mm',
                'brand' => 'Hòa Phát',
                'model' => 'CB300-V',
                'origin_country' => 'Vietnam',
                'weight_per_unit' => 1000,
                'quality_standards' => ['TCVN 1651:2018'],
                'technical_specs' => [
                    'diameter' => '12mm',
                    'yield_strength' => '300 MPa',
                    'tensile_strength' => '420-550 MPa'
                ],
                'is_featured' => false,
            ],

            // Gạch
            [
                'category_id' => $brickCategory?->id,
                'primary_unit_id' => $pieceUnit?->id,
                'material_code' => 'MAT000005',
                'name' => 'Gạch đỏ nung Đồng Tâm 220x105x60',
                'description' => 'Gạch đỏ nung kích thước 220x105x60mm, chất lượng cao',
                'brand' => 'Đồng Tâm',
                'model' => 'Standard',
                'origin_country' => 'Vietnam',
                'weight_per_unit' => 2.5,
                'dimensions' => [
                    'length' => 220,
                    'width' => 105,
                    'height' => 60
                ],
                'quality_standards' => ['TCVN 1451:2019'],
                'technical_specs' => [
                    'dimensions' => '220x105x60mm',
                    'compressive_strength' => '≥ 7.5 MPa',
                    'water_absorption' => '≤ 22%'
                ],
                'is_featured' => false,
            ],

            // Cát
            [
                'category_id' => $sandCategory?->id,
                'primary_unit_id' => $m3Unit?->id,
                'material_code' => 'MAT000006',
                'name' => 'Cát vàng xây dựng',
                'description' => 'Cát vàng tự nhiên dùng cho xây dựng, đã qua sàng lọc',
                'brand' => 'Cát Đá Miền Nam',
                'origin_country' => 'Vietnam',
                'weight_per_unit' => 1600,
                'quality_standards' => ['TCVN 7570:2006'],
                'technical_specs' => [
                    'fineness_modulus' => '2.3-3.1',
                    'clay_content' => '≤ 3%',
                    'organic_impurities' => 'Đạt yêu cầu'
                ],
                'is_featured' => false,
            ],

            // Sơn
            [
                'category_id' => $paintCategory?->id,
                'primary_unit_id' => $literUnit?->id,
                'material_code' => 'MAT000007',
                'name' => 'Sơn nội thất Jotun Essence',
                'description' => 'Sơn nước nội thất cao cấp, không mùi, thân thiện môi trường',
                'brand' => 'Jotun',
                'model' => 'Essence',
                'origin_country' => 'Vietnam',
                'weight_per_unit' => 1.2,
                'quality_standards' => ['ISO 14001', 'Green Label'],
                'technical_specs' => [
                    'coverage' => '12-14 m²/lít',
                    'dry_time' => '2-4 giờ',
                    'recoat_time' => '4-6 giờ'
                ],
                'is_featured' => true,
            ],
        ];

        foreach ($materials as $materialData) {
            if ($materialData['category_id'] && $materialData['primary_unit_id']) {
                BuildingMaterial::create(array_merge($materialData, [
                    'store_id' => $storeId,
                    'slug' => \Str::slug($materialData['name']),
                    'requires_quality_check' => true,
                    'track_batch_numbers' => true,
                    'is_active' => true,
                    'is_hazardous' => false,
                ]));
            }
        }
    }
}
