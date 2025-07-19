<?php

namespace Packages\MaterialInventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\MaterialInventory\Models\MaterialInventory;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\Store\Models\Store;

class MaterialInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = Store::active()->get();

        foreach ($stores as $store) {
            $this->createInventoryForStore($store->id);
        }
    }

    /**
     * Create inventory records for a specific store.
     */
    private function createInventoryForStore(int $storeId): void
    {
        $materials = BuildingMaterial::where('store_id', $storeId)->get();

        $locations = [
            ['code' => 'WH-A', 'name' => 'Kho A - Vật liệu khô', 'zone' => 'A'],
            ['code' => 'WH-B', 'name' => 'Kho B - Vật liệu nặng', 'zone' => 'B'],
            ['code' => 'WH-C', 'name' => 'Kho C - Vật liệu đặc biệt', 'zone' => 'C'],
            ['code' => 'YARD-1', 'name' => 'Sân 1 - Cát đá', 'zone' => 'YARD'],
            ['code' => 'YARD-2', 'name' => 'Sân 2 - Thép', 'zone' => 'YARD'],
        ];

        foreach ($materials as $material) {
            // Determine appropriate location based on material type
            $location = $this->getLocationForMaterial($material, $locations);
            
            // Generate realistic inventory data
            $inventoryData = $this->generateInventoryData($material);

            MaterialInventory::create([
                'store_id' => $storeId,
                'material_id' => $material->id,
                'location_code' => $location['code'],
                'location_name' => $location['name'],
                'zone' => $location['zone'],
                'aisle' => $this->generateAisle($location['zone']),
                'shelf' => $this->generateShelf($material),
                'bin' => $this->generateBin(),
                
                // Stock quantities
                'quantity_on_hand' => $inventoryData['on_hand'],
                'quantity_reserved' => $inventoryData['reserved'],
                'quantity_available' => $inventoryData['available'],
                'quantity_incoming' => $inventoryData['incoming'],
                'quantity_outgoing' => $inventoryData['outgoing'],
                
                // Batch tracking
                'batch_number' => $this->generateBatchNumber($material),
                'manufacture_date' => $inventoryData['manufacture_date'],
                'expiry_date' => $inventoryData['expiry_date'],
                'received_date' => $inventoryData['received_date'],
                
                // Cost tracking
                'unit_cost' => $inventoryData['unit_cost'],
                'total_cost' => $inventoryData['total_cost'],
                'cost_method' => 'fifo',
                
                // Quality & condition
                'condition' => $inventoryData['condition'],
                'quality_checked' => true,
                'last_quality_check' => now()->subDays(rand(1, 30)),
                'quality_notes' => $inventoryData['quality_notes'],
                
                // Stock levels
                'min_stock_level' => $inventoryData['min_level'],
                'max_stock_level' => $inventoryData['max_level'],
                'last_movement_at' => now()->subDays(rand(1, 7)),
                'movement_count' => rand(5, 50),
                
                'is_active' => true,
                'notes' => "Tồn kho cho {$material->name}",
            ]);
        }
    }

    /**
     * Get appropriate location for material type.
     */
    private function getLocationForMaterial(BuildingMaterial $material, array $locations): array
    {
        $materialName = strtolower($material->name);
        
        if (str_contains($materialName, 'cát') || str_contains($materialName, 'đá')) {
            return $locations[3]; // YARD-1
        } elseif (str_contains($materialName, 'thép')) {
            return $locations[4]; // YARD-2
        } elseif (str_contains($materialName, 'sơn')) {
            return $locations[2]; // WH-C
        } elseif (str_contains($materialName, 'xi măng')) {
            return $locations[0]; // WH-A
        } else {
            return $locations[1]; // WH-B
        }
    }

    /**
     * Generate realistic inventory data based on material type.
     */
    private function generateInventoryData(BuildingMaterial $material): array
    {
        $materialName = strtolower($material->name);
        
        // Base quantities based on material type
        if (str_contains($materialName, 'xi măng')) {
            $onHand = rand(100, 500);
            $unitCost = rand(95000, 120000);
        } elseif (str_contains($materialName, 'thép')) {
            $onHand = rand(5, 20);
            $unitCost = rand(16000000, 20000000);
        } elseif (str_contains($materialName, 'gạch')) {
            $onHand = rand(5000, 20000);
            $unitCost = rand(400, 600);
        } elseif (str_contains($materialName, 'cát')) {
            $onHand = rand(50, 200);
            $unitCost = rand(350000, 450000);
        } elseif (str_contains($materialName, 'sơn')) {
            $onHand = rand(20, 100);
            $unitCost = rand(150000, 300000);
        } else {
            $onHand = rand(10, 100);
            $unitCost = rand(50000, 500000);
        }

        $reserved = rand(0, intval($onHand * 0.2));
        $available = $onHand - $reserved;
        $incoming = rand(0, intval($onHand * 0.5));
        $outgoing = rand(0, intval($available * 0.3));

        return [
            'on_hand' => $onHand,
            'reserved' => $reserved,
            'available' => $available,
            'incoming' => $incoming,
            'outgoing' => $outgoing,
            'unit_cost' => $unitCost,
            'total_cost' => $onHand * $unitCost,
            'min_level' => intval($onHand * 0.2),
            'max_level' => intval($onHand * 2),
            'condition' => rand(1, 10) > 8 ? 'good' : 'new',
            'quality_notes' => 'Đã kiểm tra chất lượng, đạt tiêu chuẩn',
            'manufacture_date' => now()->subDays(rand(30, 180)),
            'expiry_date' => str_contains($materialName, 'sơn') ? now()->addYears(2) : null,
            'received_date' => now()->subDays(rand(1, 30)),
        ];
    }

    /**
     * Generate aisle based on zone.
     */
    private function generateAisle(string $zone): string
    {
        return $zone . '-' . chr(65 + rand(0, 4)); // A-E
    }

    /**
     * Generate shelf based on material.
     */
    private function generateShelf(BuildingMaterial $material): ?string
    {
        if (str_contains(strtolower($material->name), 'cát') || str_contains(strtolower($material->name), 'thép')) {
            return null; // No shelf for yard items
        }
        
        return 'S' . rand(1, 10);
    }

    /**
     * Generate bin location.
     */
    private function generateBin(): ?string
    {
        return rand(1, 10) > 7 ? 'B' . rand(1, 20) : null;
    }

    /**
     * Generate batch number.
     */
    private function generateBatchNumber(BuildingMaterial $material): string
    {
        $prefix = strtoupper(substr($material->material_code, -3));
        $date = now()->format('Ymd');
        $sequence = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return "{$prefix}{$date}{$sequence}";
    }
}
