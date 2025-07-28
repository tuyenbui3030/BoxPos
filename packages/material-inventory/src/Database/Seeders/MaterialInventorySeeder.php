<?php

namespace Packages\MaterialInventory\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Packages\MaterialInventory\Models\MaterialInventory;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\Store\Models\Store;
use Packages\Warehouse\Models\InventoryMovement;
use Packages\Warehouse\Models\StockTake;
use Packages\Warehouse\Models\StockTakeItem;
use Packages\Warehouse\Models\InventoryDisposal;
use Packages\Warehouse\Models\InventoryDisposalItem;
use Packages\User\Models\User;
use Carbon\Carbon;

class MaterialInventorySeeder extends BasePackageSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensureSeedingAllowed();
        
        $this->executeWithTransaction(function () {
            $this->seedForAllStores(function (Store $store) {
                $this->createInventoryForStore($store);
                $this->createInventoryMovementsForStore($store);
                $this->createStockTakesForStore($store);
                $this->createInventoryDisposalsForStore($store);
            });
        });
    }

    /**
     * Create inventory records for a specific store.
     */
    private function createInventoryForStore(Store $store): void
    {
        $materials = BuildingMaterial::where('store_id', $store->id)->get();

        if ($materials->isEmpty()) {
            $this->logSeedingProgress('no_materials_found', [
                'store_id' => $store->id,
                'store_name' => $store->name
            ]);
            return;
        }

        $locations = [
            ['code' => 'WH-A', 'name' => 'Kho A - Vật liệu khô', 'zone' => 'A'],
            ['code' => 'WH-B', 'name' => 'Kho B - Vật liệu nặng', 'zone' => 'B'],
            ['code' => 'WH-C', 'name' => 'Kho C - Vật liệu đặc biệt', 'zone' => 'C'],
            ['code' => 'YARD-1', 'name' => 'Sân 1 - Cát đá', 'zone' => 'YARD'],
            ['code' => 'YARD-2', 'name' => 'Sân 2 - Thép', 'zone' => 'YARD'],
        ];

        $inventoryCount = 0;
        foreach ($materials as $material) {
            // Determine appropriate location based on material type
            $location = $this->getLocationForMaterial($material, $locations);
            
            // Generate realistic inventory data
            $inventoryData = $this->generateInventoryData($material);

            MaterialInventory::create([
                'store_id' => $store->id,
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
            
            $inventoryCount++;
        }

        $this->logSeedingProgress('inventory_created', [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'inventory_count' => $inventoryCount
        ]);
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
        $prefix = strtoupper(substr($material->material_code ?? 'MAT', -3));
        $date = now()->format('Ymd');
        $sequence = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return "{$prefix}{$date}{$sequence}";
    }

    /**
     * Create inventory movements for a store.
     */
    private function createInventoryMovementsForStore(Store $store): void
    {
        $inventories = MaterialInventory::where('store_id', $store->id)->get();
        $users = User::whereHas('stores', function ($query) use ($store) {
            $query->where('store_id', $store->id);
        })->get();

        if ($inventories->isEmpty() || $users->isEmpty()) {
            $this->logSeedingProgress('skipping_movements', [
                'store_id' => $store->id,
                'reason' => 'no_inventories_or_users'
            ]);
            return;
        }

        $movementCount = $this->getRecordCount(30, 10);
        $movementTypes = ['in', 'out', 'adjustment', 'transfer'];
        $movementReasons = [
            'in' => ['purchase', 'return', 'adjustment', 'transfer_in'],
            'out' => ['sale', 'return', 'adjustment', 'transfer_out', 'disposal'],
            'adjustment' => ['adjustment', 'damage', 'theft', 'other'],
            'transfer' => ['adjustment', 'other']
        ];

        for ($i = 0; $i < $movementCount; $i++) {
            $inventory = $inventories->random();
            $user = $users->random();
            $movementType = $movementTypes[array_rand($movementTypes)];
            $reason = $movementReasons[$movementType][array_rand($movementReasons[$movementType])];
            
            $quantity = $this->generateMovementQuantity($inventory, $movementType);
            $balanceBefore = rand(0, intval($inventory->quantity_on_hand));
            $balanceAfter = $movementType === 'in' ? 
                $balanceBefore + $quantity : 
                max(0, $balanceBefore - $quantity);

            InventoryMovement::create([
                'store_id' => $store->id,
                'material_id' => $inventory->material_id,
                'movement_number' => InventoryMovement::generateMovementNumber($store->id),
                'movement_date' => now()->subDays(rand(1, 90)),
                'movement_type' => $movementType,
                'movement_reason' => $reason,
                'quantity' => $quantity,
                'unit' => $inventory->material->primaryUnit->code ?? 'pcs',
                'unit_cost' => $inventory->unit_cost,
                'total_cost' => $quantity * $inventory->unit_cost,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'batch_number' => $inventory->batch_number,
                'from_location' => $movementType === 'transfer' ? 'WH-OLD' : null,
                'to_location' => $movementType === 'transfer' ? $inventory->location_code : $inventory->location_code,
                'warehouse_zone' => $inventory->zone,
                'requires_approval' => $movementType === 'adjustment' && abs($quantity * $inventory->unit_cost) > 1000000,
                'is_approved' => true,
                'approved_by' => $user->id,
                'approved_at' => now()->subDays(rand(0, 30)),
                'is_verified' => true,
                'verified_by' => $user->id,
                'verified_at' => now()->subDays(rand(0, 30)),
                'description' => $this->getMovementDescription($movementType, $reason),
                'notes' => "Phiếu {$movementType} - {$reason}",
                'created_by' => $user->id,
            ]);
        }

        $this->logSeedingProgress('movements_created', [
            'store_id' => $store->id,
            'movement_count' => $movementCount
        ]);
    }

    /**
     * Generate movement quantity based on inventory and type.
     */
    private function generateMovementQuantity(MaterialInventory $inventory, string $movementType): float
    {
        $baseQuantity = $inventory->quantity_on_hand;
        
        return match($movementType) {
            'in' => rand(1, intval($baseQuantity * 0.5)) ?: 1,
            'out' => rand(1, intval($baseQuantity * 0.3)) ?: 1,
            'adjustment' => rand(-intval($baseQuantity * 0.1), intval($baseQuantity * 0.1)),
            'transfer' => rand(1, intval($baseQuantity * 0.2)) ?: 1,
            default => 1
        };
    }

    /**
     * Get movement description based on type and reason.
     */
    private function getMovementDescription(string $type, string $reason): string
    {
        return match($type) {
            'in' => match($reason) {
                'purchase' => 'Nhập hàng từ nhà cung cấp',
                'return' => 'Nhập hàng trả lại',
                'adjustment' => 'Điều chỉnh tăng tồn kho',
                'transfer_in' => 'Chuyển kho vào',
                default => 'Nhập kho'
            },
            'out' => match($reason) {
                'sale' => 'Xuất bán hàng',
                'return' => 'Xuất trả hàng',
                'adjustment' => 'Điều chỉnh giảm tồn kho',
                'transfer_out' => 'Chuyển kho ra',
                'disposal' => 'Xuất hủy',
                default => 'Xuất kho'
            },
            'adjustment' => match($reason) {
                'adjustment' => 'Điều chỉnh tồn kho',
                'damage' => 'Điều chỉnh do hư hỏng',
                'theft' => 'Điều chỉnh do thất thoát',
                'other' => 'Điều chỉnh khác',
                default => 'Điều chỉnh tồn kho'
            },
            'transfer' => match($reason) {
                'adjustment' => 'Chuyển đổi vị trí',
                'other' => 'Chuyển kho khác',
                default => 'Chuyển kho'
            },
            default => 'Phiếu kho'
        };
    }

    /**
     * Create stock takes for a store.
     */
    private function createStockTakesForStore(Store $store): void
    {
        $inventories = MaterialInventory::where('store_id', $store->id)->get();
        $users = User::whereHas('stores', function ($query) use ($store) {
            $query->where('store_id', $store->id);
        })->get();

        if ($inventories->isEmpty() || $users->isEmpty()) {
            $this->logSeedingProgress('skipping_stock_takes', [
                'store_id' => $store->id,
                'reason' => 'no_inventories_or_users'
            ]);
            return;
        }

        $stockTakeCount = $this->getRecordCount(3, 1);
        $stockTakeTypes = ['full', 'partial', 'cycle', 'spot'];
        $statuses = ['completed', 'in_progress'];

        for ($i = 0; $i < $stockTakeCount; $i++) {
            $supervisor = $users->random();
            $type = $stockTakeTypes[array_rand($stockTakeTypes)];
            $status = $statuses[array_rand($statuses)];
            $scheduledDate = now()->subDays(rand(1, 60));

            $stockTake = StockTake::create([
                'store_id' => $store->id,
                'stock_take_number' => StockTake::generateStockTakeNumber($store->id),
                'name' => "Kiểm kê {$this->getStockTakeTypeDisplay($type)} - " . $scheduledDate->format('d/m/Y'),
                'description' => "Kiểm kê {$this->getStockTakeTypeDisplay($type)} cho cửa hàng {$store->name}",
                'scheduled_date' => $scheduledDate,
                'start_date' => $status !== 'planned' ? $scheduledDate : null,
                'end_date' => $status === 'completed' ? $scheduledDate->addDays(rand(1, 3)) : null,
                'type' => $type,
                'status' => $status,
                'location_filter' => $type === 'partial' ? json_encode(['WH-A', 'WH-B']) : null,
                'include_zero_stock' => rand(0, 1),
                'include_negative_stock' => rand(0, 1),
                'total_materials' => $type === 'full' ? $inventories->count() : rand(5, 20),
                'supervisor_id' => $supervisor->id,
                'requires_approval' => true,
                'is_approved' => $status === 'completed',
                'approved_by' => $status === 'completed' ? $supervisor->id : null,
                'approved_at' => $status === 'completed' ? $scheduledDate->addDays(rand(1, 2)) : null,
                'auto_adjust' => rand(0, 1),
                'adjustments_posted' => $status === 'completed' && rand(0, 1),
                'notes' => "Kiểm kê định kỳ tháng " . $scheduledDate->format('m/Y'),
                'created_by' => $supervisor->id,
            ]);

            // Create stock take items
            $this->createStockTakeItems($stockTake, $inventories, $users);
        }

        $this->logSeedingProgress('stock_takes_created', [
            'store_id' => $store->id,
            'stock_take_count' => $stockTakeCount
        ]);
    }

    /**
     * Create stock take items for a stock take.
     */
    private function createStockTakeItems(StockTake $stockTake, $inventories, $users): void
    {
        $itemCount = $stockTake->type === 'full' ? 
            $inventories->count() : 
            min($stockTake->total_materials, $inventories->count());

        $selectedInventories = $stockTake->type === 'full' ? 
            $inventories : 
            $inventories->random($itemCount);

        foreach ($selectedInventories as $inventory) {
            $systemQuantity = $inventory->quantity_on_hand;
            $physicalQuantity = $stockTake->status === 'completed' ? 
                $this->generatePhysicalQuantity($systemQuantity) : 
                null;
            
            $varianceQuantity = $physicalQuantity !== null ? 
                $physicalQuantity - $systemQuantity : 0;

            $counter = $users->random();

            StockTakeItem::create([
                'stock_take_id' => $stockTake->id,
                'material_id' => $inventory->material_id,
                'material_code' => $inventory->material->material_code ?? 'N/A',
                'material_name' => $inventory->material->name,
                'system_quantity' => $systemQuantity,
                'physical_quantity' => $physicalQuantity,
                'variance_quantity' => $varianceQuantity,
                'unit' => $inventory->material->primaryUnit->code ?? 'pcs',
                'unit_cost' => $inventory->unit_cost,
                'system_value' => $systemQuantity * $inventory->unit_cost,
                'physical_value' => $physicalQuantity ? $physicalQuantity * $inventory->unit_cost : 0,
                'variance_value' => $varianceQuantity * $inventory->unit_cost,
                'count_status' => $stockTake->status === 'completed' ? 'verified' : 'pending',
                'count_attempts' => $stockTake->status === 'completed' ? rand(1, 2) : 0,
                'first_counted_at' => $stockTake->status === 'completed' ? $stockTake->start_date : null,
                'last_counted_at' => $stockTake->status === 'completed' ? $stockTake->end_date : null,
                'counted_by' => $stockTake->status === 'completed' ? $counter->id : null,
                'verified_by' => $stockTake->status === 'completed' ? $counter->id : null,
                'verified_at' => $stockTake->status === 'completed' ? $stockTake->end_date : null,
                'batch_number' => $inventory->batch_number,
                'location' => $inventory->location_code,
                'warehouse_zone' => $inventory->zone,
                'bin_location' => $inventory->bin,
                'variance_reason' => abs($varianceQuantity) > 0 ? $this->getVarianceReason($varianceQuantity) : null,
                'variance_notes' => abs($varianceQuantity) > 0 ? 'Chênh lệch phát hiện trong quá trình kiểm kê' : null,
                'requires_investigation' => abs($varianceQuantity * $inventory->unit_cost) > 100000,
                'investigation_completed' => $stockTake->status === 'completed',
                'adjustment_required' => abs($varianceQuantity) > 0,
                'adjustment_posted' => $stockTake->adjustments_posted && abs($varianceQuantity) > 0,
                'notes' => "Kiểm kê tại {$inventory->location_name}",
            ]);
        }

        // Update stock take progress
        $stockTake->updateProgress();
    }

    /**
     * Generate physical quantity with some variance.
     */
    private function generatePhysicalQuantity(float $systemQuantity): float
    {
        // 80% chance of exact match, 20% chance of variance
        if (rand(1, 100) <= 80) {
            return $systemQuantity;
        }

        // Generate variance between -10% to +5%
        $variancePercent = rand(-10, 5) / 100;
        $variance = $systemQuantity * $variancePercent;
        
        return max(0, $systemQuantity + $variance);
    }

    /**
     * Get variance reason based on quantity difference.
     */
    private function getVarianceReason(float $varianceQuantity): string
    {
        if ($varianceQuantity > 0) {
            return rand(0, 1) ? 'misplaced' : 'system_error';
        } else {
            $reasons = ['damaged', 'theft', 'system_error', 'other'];
            return $reasons[array_rand($reasons)];
        }
    }

    /**
     * Create inventory disposals for a store.
     */
    private function createInventoryDisposalsForStore(Store $store): void
    {
        $inventories = MaterialInventory::where('store_id', $store->id)->get();
        $users = User::whereHas('stores', function ($query) use ($store) {
            $query->where('store_id', $store->id);
        })->get();

        if ($inventories->isEmpty() || $users->isEmpty()) {
            $this->logSeedingProgress('skipping_disposals', [
                'store_id' => $store->id,
                'reason' => 'no_inventories_or_users'
            ]);
            return;
        }

        $disposalCount = $this->getRecordCount(2, 1);
        $disposalTypes = ['damage', 'expired', 'obsolete', 'quality_issue'];
        $disposalMethods = ['destroy', 'sell', 'return_supplier'];
        $statuses = ['completed', 'processed'];

        for ($i = 0; $i < $disposalCount; $i++) {
            $creator = $users->random();
            $type = $disposalTypes[array_rand($disposalTypes)];
            $method = $disposalMethods[array_rand($disposalMethods)];
            $status = $statuses[array_rand($statuses)];
            $disposalDate = now()->subDays(rand(1, 30));

            $disposal = InventoryDisposal::create([
                'store_id' => $store->id,
                'disposal_number' => InventoryDisposal::generateDisposalNumber($store->id),
                'title' => "Xuất hủy {$this->getDisposalTypeDisplay($type)} - " . $disposalDate->format('d/m/Y'),
                'description' => "Xuất hủy vật liệu do {$this->getDisposalTypeDisplay($type)}",
                'disposal_date' => $disposalDate,
                'disposal_type' => $type,
                'disposal_method' => $method,
                'status' => $status,
                'currency' => 'VND',
                'disposal_location' => $method === 'destroy' ? 'Khu vực tiêu hủy' : 'Kho hàng',
                'disposal_company' => $method === 'destroy' ? 'Công ty Xử lý Chất thải ABC' : null,
                'requires_approval' => true,
                'is_approved' => true,
                'approved_by' => $creator->id,
                'approved_at' => $disposalDate->addHours(rand(1, 24)),
                'inventory_adjusted' => $status === 'completed',
                'inventory_adjusted_at' => $status === 'completed' ? $disposalDate->addDays(1) : null,
                'inventory_adjusted_by' => $status === 'completed' ? $creator->id : null,
                'insurance_claim' => $type === 'damage' && rand(0, 1),
                'disposal_notes' => "Xuất hủy theo quy trình chuẩn",
                'created_by' => $creator->id,
            ]);

            // Create disposal items
            $this->createDisposalItems($disposal, $inventories);
        }

        $this->logSeedingProgress('disposals_created', [
            'store_id' => $store->id,
            'disposal_count' => $disposalCount
        ]);
    }

    /**
     * Create disposal items for a disposal.
     */
    private function createDisposalItems(InventoryDisposal $disposal, $inventories): void
    {
        $itemCount = rand(1, min(5, $inventories->count()));
        $selectedInventories = $inventories->random($itemCount);

        foreach ($selectedInventories as $inventory) {
            $quantity = rand(1, intval($inventory->quantity_on_hand * 0.1)) ?: 1;
            $unitCost = $inventory->unit_cost;
            $totalCost = $quantity * $unitCost;
            $recoveryValue = $disposal->disposal_method === 'sell' ? 
                $totalCost * rand(10, 30) / 100 : 0;

            InventoryDisposalItem::create([
                'disposal_id' => $disposal->id,
                'material_id' => $inventory->material_id,
                'material_code' => $inventory->material->material_code ?? 'N/A',
                'material_name' => $inventory->material->name,
                'material_description' => $inventory->material->description ?? '',
                'quantity' => $quantity,
                'unit' => $inventory->material->primaryUnit->code ?? 'pcs',
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'disposal_reason' => $this->getDetailedDisposalReason($disposal->disposal_type),
                'disposal_reason_detail' => $this->getDisposalReasonDetail($disposal->disposal_type),
                'condition' => $this->getDisposalCondition($disposal->disposal_type),
                'batch_number' => $inventory->batch_number,
                'location' => $inventory->location_code,
                'warehouse_zone' => $inventory->zone,
                'recovery_value' => $recoveryValue,
                'recovery_notes' => $recoveryValue > 0 ? 'Bán thanh lý với giá thấp' : null,
                'status' => $disposal->status,
                'inventory_adjusted' => $disposal->inventory_adjusted,
                'processed_at' => $disposal->status === 'completed' ? $disposal->disposal_date : null,
                'notes' => "Xuất hủy từ {$inventory->location_name}",
            ]);
        }

        // Update disposal totals
        $disposal->calculateTotals();
    }

    /**
     * Get detailed disposal reason.
     */
    private function getDetailedDisposalReason(string $type): string
    {
        return match($type) {
            'damage' => 'damaged',
            'expired' => 'expired',
            'obsolete' => 'obsolete',
            'quality_issue' => 'defective',
            default => 'other'
        };
    }

    /**
     * Get disposal reason detail.
     */
    private function getDisposalReasonDetail(string $type): string
    {
        return match($type) {
            'damage' => 'Hư hỏng do vận chuyển và bảo quản không đúng cách',
            'expired' => 'Vượt quá hạn sử dụng theo quy định',
            'obsolete' => 'Không còn phù hợp với nhu cầu thị trường',
            'quality_issue' => 'Không đạt tiêu chuẩn chất lượng yêu cầu',
            default => 'Lý do khác'
        };
    }

    /**
     * Get disposal condition.
     */
    private function getDisposalCondition(string $type): string
    {
        return match($type) {
            'damage' => 'damaged',
            'expired' => 'expired',
            'obsolete' => 'good',
            'quality_issue' => 'poor',
            default => 'fair'
        };
    }

    /**
     * Get stock take type display.
     */
    private function getStockTakeTypeDisplay(string $type): string
    {
        return match($type) {
            'full' => 'toàn bộ',
            'partial' => 'một phần',
            'cycle' => 'chu kỳ',
            'spot' => 'đột xuất',
            default => $type
        };
    }

    /**
     * Get disposal type display.
     */
    private function getDisposalTypeDisplay(string $type): string
    {
        return match($type) {
            'damage' => 'hư hỏng',
            'expired' => 'hết hạn',
            'obsolete' => 'lỗi thời',
            'quality_issue' => 'vấn đề chất lượng',
            default => $type
        };
    }
}
