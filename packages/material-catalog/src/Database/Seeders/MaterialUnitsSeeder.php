<?php

namespace Packages\MaterialCatalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\MaterialCatalog\Models\MaterialUnit;
use Packages\Store\Models\Store;

class MaterialUnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = Store::active()->get();

        foreach ($stores as $store) {
            $this->createUnitsForStore($store->id);
        }
    }

    /**
     * Create material units for a specific store.
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
    }
}
