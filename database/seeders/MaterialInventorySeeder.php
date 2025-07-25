<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\MaterialInventory\Models\MaterialInventory;
use Carbon\Carbon;

class MaterialInventorySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📦 Seeding Material Inventory...');

        $stores = Store::all();

        foreach ($stores as $store) {
            $this->createInventoryForStore($store);
        }

        $this->command->info('✅ Material Inventory seeded successfully!');
    }

    private function createInventoryForStore(Store $store): void
    {
        $materials = BuildingMaterial::where('store_id', $store->id)->get();

        $locations = [
            [
                'location_code' => 'WH-A01',
                'location_name' => 'Kho chính - Khu A - Dãy 01',
                'zone' => 'Zone A',
                'aisle' => 'A01',
                'shelf' => 'S01',
                'bin' => 'B01',
            ],
            [
                'location_code' => 'WH-A02',
                'location_name' => 'Kho chính - Khu A - Dãy 02',
                'zone' => 'Zone A',
                'aisle' => 'A02',
                'shelf' => 'S01',
                'bin' => 'B01',
            ],
            [
                'location_code' => 'WH-B01',
                'location_name' => 'Kho phụ - Khu B - Dãy 01',
                'zone' => 'Zone B',
                'aisle' => 'B01',
                'shelf' => 'S01',
                'bin' => 'B01',
            ],
            [
                'location_code' => 'WH-C01',
                'location_name' => 'Kho ngoài trời - Khu C',
                'zone' => 'Zone C',
                'aisle' => 'C01',
                'shelf' => null,
                'bin' => null,
            ],
            [
                'location_code' => 'SHOP-01',
                'location_name' => 'Showroom - Kệ trưng bày 01',
                'zone' => 'Showroom',
                'aisle' => 'SR01',
                'shelf' => 'Display',
                'bin' => 'D01',
            ],
        ];

        foreach ($materials as $material) {
            // Create inventory records for different locations
            $locationCount = rand(1, 3); // Each material in 1-3 locations
            $selectedLocations = collect($locations)->random($locationCount);

            foreach ($selectedLocations as $location) {
                $this->createInventoryRecord($store, $material, $location);
            }
        }
    }

    private function createInventoryRecord(Store $store, BuildingMaterial $material, array $location): void
    {
        // Generate realistic quantities based on material type
        $baseQuantity = $this->getBaseQuantityByMaterial($material);
        $quantityOnHand = $baseQuantity + rand(-$baseQuantity * 0.3, $baseQuantity * 0.5);
        $quantityReserved = rand(0, $quantityOnHand * 0.2);
        $quantityAvailable = $quantityOnHand - $quantityReserved;
        $quantityIncoming = rand(0, $baseQuantity * 0.3);

        // Generate batch information
        $batchNumber = $this->generateBatchNumber($material);
        $manufactureDate = Carbon::now()->subDays(rand(30, 180));
        $receivedDate = $manufactureDate->copy()->addDays(rand(1, 30));
        $expiryDate = $material->shelf_life_days ? 
            $manufactureDate->copy()->addDays($material->shelf_life_days) : null;

        // Calculate costs
        $unitCost = rand(10000, 500000); // VND
        $totalCost = $quantityOnHand * $unitCost;

        // Determine condition based on age and expiry
        $condition = $this->determineCondition($receivedDate, $expiryDate);

        // Set stock levels
        $minStockLevel = $baseQuantity * 0.2;
        $maxStockLevel = $baseQuantity * 2;

        MaterialInventory::create([
            'store_id' => $store->id,
            'material_id' => $material->id,
            'location_code' => $location['location_code'],
            'location_name' => $location['location_name'],
            'zone' => $location['zone'],
            'aisle' => $location['aisle'],
            'shelf' => $location['shelf'],
            'bin' => $location['bin'],
            'quantity_on_hand' => $quantityOnHand,
            'quantity_reserved' => $quantityReserved,
            'quantity_available' => $quantityAvailable,
            'quantity_incoming' => $quantityIncoming,
            'quantity_outgoing' => 0,
            'batch_number' => $batchNumber,
            'serial_numbers' => $material->track_serial_numbers ? 
                json_encode($this->generateSerialNumbers($quantityOnHand)) : null,
            'manufacture_date' => $manufactureDate,
            'expiry_date' => $expiryDate,
            'received_date' => $receivedDate,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'cost_method' => 'fifo',
            'condition' => $condition,
            'quality_checked' => rand(0, 1) == 1,
            'last_quality_check' => rand(0, 1) == 1 ? Carbon::now()->subDays(rand(1, 30)) : null,
            'quality_notes' => $condition === 'good' ? 'Chất lượng tốt' : 
                ($condition === 'fair' ? 'Chất lượng khá' : null),
            'min_stock_level' => $minStockLevel,
            'max_stock_level' => $maxStockLevel,
            'last_movement_at' => $receivedDate,
            'movement_count' => rand(1, 10),
            'is_active' => true,
            'notes' => $this->generateInventoryNotes($location, $condition),
        ]);
    }

    private function getBaseQuantityByMaterial(BuildingMaterial $material): int
    {
        // Different base quantities based on material category
        $categoryName = $material->category->name ?? '';
        
        if (str_contains(strtolower($categoryName), 'thép')) {
            return rand(50, 200); // Steel bars/sheets
        } elseif (str_contains(strtolower($categoryName), 'xi măng')) {
            return rand(100, 500); // Cement bags
        } elseif (str_contains(strtolower($categoryName), 'gạch')) {
            return rand(500, 2000); // Bricks/tiles
        } elseif (str_contains(strtolower($categoryName), 'sơn')) {
            return rand(20, 100); // Paint cans
        } elseif (str_contains(strtolower($categoryName), 'điện')) {
            return rand(10, 50); // Electrical items
        } else {
            return rand(50, 300); // Default
        }
    }

    private function generateBatchNumber(BuildingMaterial $material): string
    {
        $prefix = strtoupper(substr($material->material_code, 0, 3));
        $date = Carbon::now()->format('Ymd');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$random}";
    }

    private function determineCondition(Carbon $receivedDate, ?Carbon $expiryDate): string
    {
        $now = Carbon::now();
        $daysSinceReceived = $receivedDate->diffInDays($now);
        
        if ($expiryDate && $expiryDate->isPast()) {
            return 'expired';
        }
        
        if ($daysSinceReceived > 365) {
            return 'fair';
        } elseif ($daysSinceReceived > 180) {
            return 'good';
        } else {
            return 'new';
        }
    }

    private function generateSerialNumbers(int $quantity): array
    {
        $serials = [];
        for ($i = 1; $i <= min($quantity, 10); $i++) { // Max 10 serials for demo
            $serials[] = 'SN' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        }
        return $serials;
    }

    private function generateInventoryNotes(array $location, string $condition): ?string
    {
        $notes = [];
        
        if ($location['zone'] === 'Zone C') {
            $notes[] = 'Lưu trữ ngoài trời - cần kiểm tra thường xuyên';
        }
        
        if ($condition === 'fair') {
            $notes[] = 'Cần kiểm tra chất lượng định kỳ';
        }
        
        if ($location['location_code'] === 'SHOP-01') {
            $notes[] = 'Hàng trưng bày - không bán';
        }
        
        return empty($notes) ? null : implode('. ', $notes);
    }
}
