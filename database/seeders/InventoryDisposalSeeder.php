<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\Warehouse\Models\InventoryDisposal;
use Packages\Warehouse\Models\InventoryDisposalItem;
use Packages\User\Models\User;
use Carbon\Carbon;

class InventoryDisposalSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🗑️ Seeding Inventory Disposals...');

        $stores = Store::all();

        foreach ($stores as $store) {
            $this->createDisposalsForStore($store);
        }

        $this->command->info('✅ Inventory Disposals seeded successfully!');
    }

    private function createDisposalsForStore(Store $store): void
    {
        $materials = BuildingMaterial::where('store_id', $store->id)->get();
        $createdBy = User::where('email', 'admin@' . $store->domain)->first();

        if ($materials->isEmpty()) {
            return;
        }

        // Create 2-4 disposals per store
        $disposalCount = rand(2, 4);

        for ($i = 1; $i <= $disposalCount; $i++) {
            $this->createDisposal($store, $materials, $createdBy, $i);
        }
    }

    private function createDisposal(Store $store, $materials, ?User $createdBy, int $sequence): void
    {
        $disposalDate = Carbon::now()->subDays(rand(7, 60));
        $disposalNumber = $this->generateDisposalNumber($store, $disposalDate, $sequence);
        
        $disposal = InventoryDisposal::create([
            'store_id' => $store->id,
            'disposal_number' => $disposalNumber,
            'title' => 'Thanh lý tài sản ' . $disposalDate->format('d/m/Y'),
            'description' => $this->generateDisposalNotes(),
            'disposal_date' => $disposalDate,
            'disposal_type' => $this->getRandomDisposalType(),
            'disposal_method' => $this->getDisposalMethod(),
            'status' => $this->getDisposalStatus($disposalDate),
            'total_cost_value' => 0, // Will be calculated after items
            'recovery_value' => 0,
            'net_loss' => 0,
            'disposal_location' => $this->getDisposalLocation(),
            'disposal_notes' => $this->generateDisposalNotes(),
            'created_by' => $createdBy?->id,
            'approved_by' => $createdBy?->id,
            'approved_at' => $disposalDate->copy()->addHours(rand(2, 6)),
            'metadata' => json_encode([
                'created_via' => 'seeder',
            ]),
        ]);

        // Create disposal items
        $this->createDisposalItems($disposal, $materials);
        
        // Update totals
        $this->updateDisposalTotals($disposal);
    }

    private function createDisposalItems(InventoryDisposal $disposal, $materials): void
    {
        // Dispose 3-8 items per disposal (but not more than available)
        $maxItems = min(8, $materials->count());
        $itemCount = rand(3, $maxItems);
        $selectedMaterials = $materials->random($itemCount);

        foreach ($selectedMaterials as $material) {
            $this->createDisposalItem($disposal, $material);
        }
    }

    private function createDisposalItem(InventoryDisposal $disposal, BuildingMaterial $material): void
    {
        $quantity = rand(1, 20); // Small quantities for disposal
        $unitCost = rand(10000, 500000);
        $totalValue = $quantity * $unitCost;

        InventoryDisposalItem::create([
            'disposal_id' => $disposal->id,
            'material_id' => $material->id,
            'material_code' => $material->code ?? 'MAT-' . $material->id,
            'material_name' => $material->name,
            'material_description' => $material->description,
            'quantity' => $quantity,
            'unit' => $material->unit ?? 'pcs',
            'unit_cost' => $unitCost,
            'total_cost' => $totalValue,
            'disposal_reason' => $this->getDisposalReason($disposal->disposal_type),
            'condition' => $this->getItemCondition($disposal->disposal_type),
            'batch_number' => $this->generateBatchNumber($material),
            'expiry_date' => $this->getExpiryDate($material),
            'notes' => $this->generateItemNotes($material, $disposal->disposal_type),
        ]);
    }

    private function updateDisposalTotals(InventoryDisposal $disposal): void
    {
        $items = $disposal->items;
        $totalCostValue = $items->sum('total_cost');
        $recoveryValue = $totalCostValue * 0.1; // Assume 10% recovery value
        $netLoss = $totalCostValue - $recoveryValue;

        $disposal->update([
            'total_cost_value' => $totalCostValue,
            'recovery_value' => $recoveryValue,
            'net_loss' => $netLoss,
        ]);
    }

    private function generateDisposalNumber(Store $store, Carbon $disposalDate, int $sequence): string
    {
        $storeCode = strtoupper(substr($store->slug, 0, 3));
        $dateCode = $disposalDate->format('Ymd');
        $sequenceCode = str_pad($sequence, 2, '0', STR_PAD_LEFT);
        
        return "DSP-{$storeCode}-{$dateCode}-{$sequenceCode}";
    }

    private function getRandomDisposalType(): string
    {
        // Available types: 'damage', 'expired', 'obsolete', 'theft', 'loss', 'quality_issue', 'other'
        $types = ['damage', 'expired', 'obsolete', 'theft', 'loss', 'quality_issue', 'other'];
        return $types[array_rand($types)];
    }



    private function getDisposalStatus(Carbon $disposalDate): string
    {
        $daysSince = $disposalDate->diffInDays(Carbon::now());
        
        if ($daysSince < 1) {
            return 'pending';
        } elseif ($daysSince < 3) {
            return 'approved';
        } else {
            return 'completed';
        }
    }

    private function generateDisposalNotes(): string
    {
        $notes = [
            'Xử lý theo quy định về môi trường',
            'Đã thông báo cho bộ phận kế toán',
            'Lưu trữ chứng từ để kiểm toán',
            'Tuân thủ quy định pháp luật',
            'Đã có sự chứng kiến của bên thứ ba',
        ];
        
        return $notes[array_rand($notes)];
    }

    private function getDisposalMethod(): string
    {
        // Available methods: 'destroy', 'sell', 'donate', 'return_supplier', 'recycle', 'other'
        $methods = ['destroy', 'sell', 'donate', 'return_supplier', 'recycle', 'other'];
        return $methods[array_rand($methods)];
    }

    private function getDisposalLocation(): string
    {
        $locations = [
            'Kho chính',
            'Kho phụ',
            'Bãi rác công ty',
            'Trung tâm tái chế',
            'Kho hàng hỏng',
            'Ngoài trời',
        ];
        return $locations[array_rand($locations)];
    }

    private function getDisposalReason(string $disposalType): string
    {
        // Available reasons: 'damaged', 'expired', 'obsolete', 'defective', 'contaminated', 'theft', 'loss', 'quality_issue', 'recall', 'other'
        $reasonMap = [
            'damage' => ['damaged', 'defective'],
            'expired' => ['expired'],
            'obsolete' => ['obsolete'],
            'theft' => ['theft'],
            'loss' => ['loss'],
            'quality_issue' => ['quality_issue', 'defective'],
            'other' => ['other', 'contaminated', 'recall'],
        ];

        $reasons = $reasonMap[$disposalType] ?? ['other'];
        return $reasons[array_rand($reasons)];
    }

    private function generateBatchNumber(BuildingMaterial $material): ?string
    {
        if (!$material->track_batch_numbers) {
            return null;
        }
        
        $prefix = strtoupper(substr($material->material_code, 0, 3));
        $date = Carbon::now()->subDays(rand(90, 365))->format('Ymd');
        $sequence = str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$sequence}";
    }

    private function getExpiryDate(BuildingMaterial $material): ?Carbon
    {
        if (!$material->shelf_life_days) {
            return null;
        }
        
        // Expired items
        return Carbon::now()->subDays(rand(1, 30));
    }

    private function getItemCondition(string $disposalType): string
    {
        // Available conditions: 'damaged', 'expired', 'good', 'fair', 'poor'
        return match($disposalType) {
            'damage' => 'damaged',
            'expired' => 'expired',
            'obsolete' => 'poor',
            'theft', 'loss' => 'fair', // Items were in fair condition before being lost/stolen
            'quality_issue' => 'poor',
            default => 'poor',
        };
    }

    private function generateItemNotes(BuildingMaterial $material, string $disposalType): ?string
    {
        $notes = [
            'damage' => 'Hàng bị hư hỏng không thể sử dụng',
            'expiry' => 'Đã hết hạn sử dụng',
            'obsolete' => 'Hàng lỗi thời, không có nhu cầu',
            'theft' => 'Mất do trộm cắp',
            'loss' => 'Mất mát không rõ nguyên nhân',
        ];
        
        return $notes[$disposalType] ?? null;
    }
}
