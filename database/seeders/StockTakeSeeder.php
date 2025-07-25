<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\Warehouse\Models\StockTake;
use Packages\Warehouse\Models\StockTakeItem;
use Packages\User\Models\User;
use Carbon\Carbon;

class StockTakeSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📋 Seeding Stock Takes...');

        $stores = Store::all();

        foreach ($stores as $store) {
            $this->createStockTakesForStore($store);
        }

        $this->command->info('✅ Stock Takes seeded successfully!');
    }

    private function createStockTakesForStore(Store $store): void
    {
        $materials = BuildingMaterial::where('store_id', $store->id)->get();
        $createdBy = User::where('email', 'admin@' . $store->domain)->first();

        if ($materials->isEmpty()) {
            return;
        }

        // Create 3-5 stock takes for each store
        $stockTakeCount = rand(3, 5);

        for ($i = 1; $i <= $stockTakeCount; $i++) {
            $this->createStockTake($store, $materials, $createdBy, $i);
        }
    }

    private function createStockTake(Store $store, $materials, ?User $createdBy, int $sequence): void
    {
        $stockTakeDate = Carbon::now()->subDays(rand(7, 90));
        $stockTakeNumber = $this->generateStockTakeNumber($store, $stockTakeDate, $sequence);
        
        $stockTake = StockTake::create([
            'store_id' => $store->id,
            'stock_take_number' => $stockTakeNumber,
            'name' => 'Kiểm kho ' . $stockTakeDate->format('d/m/Y'),
            'description' => $this->generateStockTakeNotes(),
            'scheduled_date' => $stockTakeDate,
            'start_date' => $stockTakeDate,
            'end_date' => $stockTakeDate->copy()->addHours(rand(2, 8)),
            'type' => $this->getRandomStockTakeType(),
            'status' => $this->getStockTakeStatus($stockTakeDate),
            'location_filter' => $this->getRandomLocation(),
            'total_materials' => 0, // Will be updated after items
            'counted_materials' => 0,
            'variance_count' => 0,
            'total_variance_value' => 0, // Will be calculated after items
            'supervisor_id' => $createdBy?->id,
            'is_approved' => true,
            'approved_by' => $createdBy?->id,
            'approved_at' => $stockTakeDate->copy()->addHours(rand(4, 8)),
            'metadata' => json_encode([
                'created_via' => 'seeder',
                'counting_method' => 'manual',
                'cycle_count' => rand(0, 1) == 1,
            ]),
        ]);

        // Create stock take items
        $this->createStockTakeItems($stockTake, $materials);
        
        // Update totals
        $this->updateStockTakeTotals($stockTake);
    }

    private function createStockTakeItems(StockTake $stockTake, $materials): void
    {
        // Count 20-50% of materials in each stock take
        $itemCount = rand(ceil($materials->count() * 0.2), ceil($materials->count() * 0.5));
        $selectedMaterials = $materials->random($itemCount);

        foreach ($selectedMaterials as $material) {
            $this->createStockTakeItem($stockTake, $material);
        }
    }

    private function createStockTakeItem(StockTake $stockTake, BuildingMaterial $material): void
    {
        $systemQuantity = rand(10, 500); // Simulated system quantity
        $countedQuantity = $this->getCountedQuantity($systemQuantity);
        $variance = $countedQuantity - $systemQuantity;
        $unitCost = rand(10000, 500000);
        $varianceValue = $variance * $unitCost;

        StockTakeItem::create([
            'stock_take_id' => $stockTake->id,
            'material_id' => $material->id,
            'material_code' => $material->code ?? 'MAT-' . $material->id,
            'material_name' => $material->name,
            'system_quantity' => $systemQuantity,
            'physical_quantity' => $countedQuantity,
            'variance_quantity' => $variance,
            'unit' => $material->unit ?? 'pcs',
            'unit_cost' => $unitCost,
            'system_value' => $systemQuantity * $unitCost,
            'physical_value' => $countedQuantity * $unitCost,
            'variance_value' => $varianceValue,
            'count_status' => 'verified',
            'count_attempts' => 1,
            'first_counted_at' => $stockTake->start_date ? Carbon::parse($stockTake->start_date)->addMinutes(rand(30, 240)) : null,
            'last_counted_at' => $stockTake->start_date ? Carbon::parse($stockTake->start_date)->addMinutes(rand(30, 240)) : null,
            'counted_by' => $stockTake->supervisor_id,
            'verified_by' => $stockTake->approved_by,
            'verified_at' => $stockTake->approved_at,
            'batch_number' => $this->generateBatchNumber($material),
            'location' => $stockTake->location_filter,
            'variance_reason' => $this->getVarianceReason($variance),
            'notes' => $this->generateItemNotes($variance, $material),
        ]);
    }

    private function updateStockTakeTotals(StockTake $stockTake): void
    {
        $items = $stockTake->items;
        $totalMaterials = $items->count();
        $countedMaterials = $items->where('count_status', '!=', 'pending')->count();
        $varianceCount = $items->where('variance_quantity', '!=', 0)->count();
        $totalVarianceValue = $items->sum('variance_value');

        $stockTake->update([
            'total_materials' => $totalMaterials,
            'counted_materials' => $countedMaterials,
            'variance_count' => $varianceCount,
            'total_variance_value' => $totalVarianceValue,
        ]);
    }

    private function generateStockTakeNumber(Store $store, Carbon $stockTakeDate, int $sequence): string
    {
        $storeCode = strtoupper(substr($store->slug, 0, 3));
        $dateCode = $stockTakeDate->format('Ymd');
        $sequenceCode = str_pad($sequence, 2, '0', STR_PAD_LEFT);
        
        return "ST-{$storeCode}-{$dateCode}-{$sequenceCode}";
    }

    private function getRandomStockTakeType(): string
    {
        $types = ['full', 'partial', 'cycle', 'spot'];
        $weights = [20, 40, 30, 10]; // Weighted random
        
        $random = rand(1, 100);
        $cumulative = 0;
        
        foreach ($types as $index => $type) {
            $cumulative += $weights[$index];
            if ($random <= $cumulative) {
                return $type;
            }
        }
        
        return 'partial';
    }

    private function getStockTakeStatus(Carbon $stockTakeDate): string
    {
        $daysSince = $stockTakeDate->diffInDays(Carbon::now());
        
        if ($daysSince < 1) {
            return 'in_progress';
        } elseif ($daysSince < 3) {
            return 'completed';
        } else {
            return 'approved';
        }
    }

    private function getRandomLocation(): string
    {
        $locations = [
            'WH-A01 - Kho chính A',
            'WH-B01 - Kho phụ B', 
            'WH-C01 - Kho ngoài trời',
            'SHOP-01 - Showroom',
            'ALL - Toàn bộ kho',
        ];
        
        return $locations[array_rand($locations)];
    }

    private function getStockTakeReason(): string
    {
        $reasons = [
            'Kiểm kho định kỳ hàng tháng',
            'Kiểm kho cuối năm',
            'Kiểm kho sau sự cố',
            'Kiểm kho theo yêu cầu',
            'Kiểm kho trước khi chuyển kho',
            'Kiểm kho sau nhập hàng lớn',
            'Kiểm kho ngẫu nhiên',
        ];
        
        return $reasons[array_rand($reasons)];
    }

    private function generateStockTakeNotes(): string
    {
        $notes = [
            'Kiểm kho thực hiện trong giờ hành chính',
            'Tạm dừng hoạt động xuất nhập kho trong thời gian kiểm',
            'Sử dụng máy quét mã vạch để kiểm đếm',
            'Kiểm tra kỹ các vị trí khó tiếp cận',
            'Đối chiếu với phiếu xuất nhập gần nhất',
        ];
        
        return $notes[array_rand($notes)];
    }

    private function getCountedQuantity(float $systemQuantity): float
    {
        // 70% chance of exact match, 30% chance of variance
        if (rand(1, 100) <= 70) {
            return $systemQuantity; // Exact match
        }
        
        // Create variance: -20% to +10%
        $variancePercent = rand(-20, 10) / 100;
        $variance = $systemQuantity * $variancePercent;
        
        return max(0, $systemQuantity + $variance);
    }

    private function generateBatchNumber(BuildingMaterial $material): ?string
    {
        if (!$material->track_batch_numbers) {
            return null;
        }
        
        $prefix = strtoupper(substr($material->material_code, 0, 3));
        $date = Carbon::now()->subDays(rand(30, 180))->format('Ymd');
        $sequence = str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$sequence}";
    }

    private function getVarianceReason(float $variance): string
    {
        // Available reasons: 'none', 'damaged', 'expired', 'theft', 'misplaced', 'system_error', 'receiving_error', 'shipping_error', 'other'
        if ($variance == 0) {
            return 'none';
        } elseif ($variance > 0) {
            $reasons = ['misplaced', 'system_error', 'receiving_error', 'other'];
            return $reasons[array_rand($reasons)];
        } else {
            $reasons = ['damaged', 'expired', 'theft', 'system_error', 'shipping_error'];
            return $reasons[array_rand($reasons)];
        }
    }

    private function generateItemNotes(float $variance, BuildingMaterial $material): ?string
    {
        if ($variance == 0) {
            return 'Số lượng khớp với hệ thống';
        }
        
        $notes = [];
        
        if ($variance > 0) {
            $notes[] = "Thừa {$variance} đơn vị so với hệ thống";
            $notes[] = 'Cần kiểm tra lại phiếu xuất nhập gần đây';
        } else {
            $notes[] = "Thiếu " . abs($variance) . " đơn vị so với hệ thống";
            $notes[] = 'Cần điều tra nguyên nhân thiếu hụt';
        }
        
        if (abs($variance) > 50) {
            $notes[] = 'Chênh lệch lớn - cần xác minh lại';
        }
        
        return implode('. ', $notes);
    }
}
