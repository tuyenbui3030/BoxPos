<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\Warehouse\Models\InventoryMovement;
use Packages\User\Models\User;
use Carbon\Carbon;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📦 Seeding Warehouse Inventory Movements...');

        $stores = Store::all();

        foreach ($stores as $store) {
            $this->createInventoryMovementsForStore($store);
        }

        $this->command->info('✅ Warehouse Inventory Movements seeded successfully!');
    }

    private function createInventoryMovementsForStore(Store $store): void
    {
        $materials = BuildingMaterial::where('store_id', $store->id)->get();
        $createdBy = User::where('email', 'admin@' . $store->domain)->first();

        if ($materials->isEmpty()) {
            return;
        }

        // Create movements for the last 60 days
        $startDate = Carbon::now()->subDays(60);
        $endDate = Carbon::now();

        // Create 50-100 movements per store
        $movementCount = rand(50, 100);

        for ($i = 0; $i < $movementCount; $i++) {
            $material = $materials->random();
            $movementDate = $this->getRandomDate($startDate, $endDate);
            $this->createInventoryMovement($store, $material, $movementDate, $createdBy);
        }
    }

    private function createInventoryMovement(Store $store, BuildingMaterial $material, Carbon $movementDate, ?User $createdBy): void
    {
        $movementType = $this->getRandomMovementType();
        $quantity = $this->getQuantityForMovementType($movementType, $material);
        $unitCost = rand(10000, 500000); // VND
        $totalCost = $quantity * $unitCost;

        // Generate unique movement number
        $maxAttempts = 10;
        $attempts = 0;
        do {
            $movementNumber = $this->generateMovementNumber($movementType, $movementDate);
            $exists = InventoryMovement::where('store_id', $store->id)
                ->where('movement_number', $movementNumber)
                ->exists();
            $attempts++;
        } while ($exists && $attempts < $maxAttempts);

        if ($exists) {
            return; // Skip if can't generate unique number
        }

        InventoryMovement::create([
            'store_id' => $store->id,
            'material_id' => $material->id,
            'movement_number' => $movementNumber,
            'movement_date' => $movementDate,
            'movement_type' => $movementType,
            'movement_reason' => $this->getMovementReason($movementType),
            'quantity' => $quantity,
            'unit' => $material->unit ?? 'pcs',
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'balance_before' => 0,
            'balance_after' => 0,
            'related_document_type' => $this->getRelatedDocumentType($movementType),
            'related_document_id' => $this->getRelatedDocumentId($movementType),
            'batch_number' => $this->generateBatchNumber($material, $movementDate),
            'expiry_date' => $this->getExpiryDate($material, $movementDate),
            'notes' => $this->generateMovementNotes($movementType, $quantity),
            'created_by' => $createdBy?->id,
            'metadata' => json_encode([
                'created_via' => 'seeder',
                'movement_category' => $this->getMovementCategory($movementType),
                'cost_method' => 'fifo',
            ]),
        ]);
    }

    private function getRandomDate(Carbon $startDate, Carbon $endDate): Carbon
    {
        $daysDiff = $startDate->diffInDays($endDate);
        $randomDays = rand(0, $daysDiff);
        
        return $startDate->copy()->addDays($randomDays)->addHours(rand(8, 17));
    }

    private function getRandomMovementType(): string
    {
        $types = [
            'in' => 40,      // 40% - Stock in
            'out' => 35,     // 35% - Stock out
            'transfer' => 10, // 10% - Transfer
            'adjustment' => 8, // 8% - Adjustment
            'return' => 4,   // 4% - Return
            'disposal' => 3,   // 3% - Damage/Loss
        ];

        $random = rand(1, 100);
        $cumulative = 0;

        foreach ($types as $type => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $type;
            }
        }

        return 'in'; // Default
    }

    private function getQuantityForMovementType(string $movementType, BuildingMaterial $material): float
    {
        $categoryName = $material->category->name ?? '';
        
        // Base quantity based on material category
        if (str_contains(strtolower($categoryName), 'thép')) {
            $baseQuantity = rand(5, 50);
        } elseif (str_contains(strtolower($categoryName), 'xi măng')) {
            $baseQuantity = rand(20, 200);
        } elseif (str_contains(strtolower($categoryName), 'gạch')) {
            $baseQuantity = rand(50, 500);
        } else {
            $baseQuantity = rand(10, 100);
        }

        // Adjust based on movement type
        return match($movementType) {
            'in' => $baseQuantity * rand(1, 3), // Larger quantities for stock in
            'out' => $baseQuantity * rand(1, 2), // Medium quantities for stock out
            'transfer' => $baseQuantity, // Normal quantities for transfer
            'adjustment' => rand(1, $baseQuantity * 0.5), // Small adjustments
            'return' => rand(1, $baseQuantity * 0.3), // Small returns
            'disposal' => rand(1, $baseQuantity * 0.2), // Small disposal quantities
            default => $baseQuantity,
        };
    }

    private function getMovementReason(string $movementType): string
    {
        return match($movementType) {
            'in' => ['purchase', 'return', 'adjustment'][rand(0, 2)],
            'out' => ['sale', 'transfer_out', 'disposal'][rand(0, 2)],
            'transfer' => rand(0, 1) == 1 ? 'transfer_in' : 'transfer_out',
            'adjustment' => 'adjustment',
            'return' => 'return',
            'disposal' => ['disposal', 'damage', 'expired'][rand(0, 2)],
            default => 'other',
        };
    }

    private function getRelatedDocumentType(string $movementType): ?string
    {
        return match($movementType) {
            'in' => rand(0, 1) == 1 ? 'purchase_order' : 'stock_take',
            'out' => rand(0, 1) == 1 ? 'sales_order' : 'invoice',
            'transfer' => 'transfer_order',
            'adjustment' => 'stock_adjustment',
            'return' => 'sales_return',
            'disposal' => 'disposal_order',
            default => null,
        };
    }

    private function getRelatedDocumentId(string $movementType): ?int
    {
        // Generate fake document IDs
        return rand(1, 1000);
    }

    private function generateMovementNumber(string $movementType, Carbon $movementDate): string
    {
        $prefix = match($movementType) {
            'in' => 'IN',
            'out' => 'OUT',
            'transfer' => 'TRF',
            'adjustment' => 'ADJ',
            'return' => 'RET',
            'damage' => 'DMG',
            default => 'MOV',
        };

        $dateCode = $movementDate->format('Ymd');
        $sequence = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        return "{$prefix}-{$dateCode}-{$sequence}";
    }

    private function getLocationFrom(string $movementType): ?string
    {
        return match($movementType) {
            'in' => null, // External source
            'out' => $this->getRandomWarehouseLocation(),
            'transfer' => $this->getRandomWarehouseLocation(),
            'adjustment' => $this->getRandomWarehouseLocation(),
            'return' => 'CUSTOMER',
            'damage' => $this->getRandomWarehouseLocation(),
            default => null,
        };
    }

    private function getLocationTo(string $movementType): ?string
    {
        return match($movementType) {
            'in' => $this->getRandomWarehouseLocation(),
            'out' => null, // External destination
            'transfer' => $this->getRandomWarehouseLocation(),
            'adjustment' => $this->getRandomWarehouseLocation(),
            'return' => $this->getRandomWarehouseLocation(),
            'damage' => 'DISPOSAL',
            default => null,
        };
    }

    private function getRandomWarehouseLocation(): string
    {
        $locations = [
            'WH-A01',
            'WH-A02',
            'WH-B01',
            'WH-C01',
            'SHOP-01',
            'YARD-01',
            'STORAGE-01',
        ];

        return $locations[array_rand($locations)];
    }

    private function generateBatchNumber(BuildingMaterial $material, Carbon $movementDate): ?string
    {
        if (!$material->track_batch_numbers) {
            return null;
        }

        $prefix = strtoupper(substr($material->material_code, 0, 3));
        $dateCode = $movementDate->format('Ymd');
        $sequence = str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);

        return "{$prefix}-{$dateCode}-{$sequence}";
    }

    private function getExpiryDate(BuildingMaterial $material, Carbon $movementDate): ?Carbon
    {
        if (!$material->shelf_life_days) {
            return null;
        }

        return $movementDate->copy()->addDays($material->shelf_life_days);
    }

    private function generateMovementNotes(string $movementType, float $quantity): ?string
    {
        $notes = [];

        if ($quantity > 100) {
            $notes[] = 'Số lượng lớn';
        }

        if ($movementType === 'damage') {
            $notes[] = 'Cần xử lý hàng hỏng';
        }

        if ($movementType === 'adjustment') {
            $notes[] = 'Điều chỉnh sau kiểm kho định kỳ';
        }

        if ($movementType === 'transfer') {
            $notes[] = 'Chuyển kho nội bộ';
        }

        return empty($notes) ? null : implode('. ', $notes);
    }

    private function getMovementCategory(string $movementType): string
    {
        return match($movementType) {
            'in', 'return' => 'inbound',
            'out', 'damage' => 'outbound',
            'transfer', 'adjustment' => 'internal',
            default => 'other',
        };
    }
}
