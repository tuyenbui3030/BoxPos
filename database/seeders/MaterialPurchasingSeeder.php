<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\MaterialSuppliers\Models\MaterialSupplier;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\MaterialPurchasing\Models\MaterialPurchaseOrder;
use Packages\MaterialPurchasing\Models\MaterialPurchaseOrderItem;
use Packages\User\Models\User;
use Carbon\Carbon;

class MaterialPurchasingSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🛒 Seeding Material Purchase Orders...');

        $stores = Store::all();

        foreach ($stores as $store) {
            $this->createPurchaseOrdersForStore($store);
        }

        $this->command->info('✅ Material Purchase Orders seeded successfully!');
    }

    private function createPurchaseOrdersForStore(Store $store): void
    {
        $suppliers = MaterialSupplier::where('store_id', $store->id)->get();
        $materials = BuildingMaterial::where('store_id', $store->id)->get();
        $createdBy = User::where('email', 'admin@' . $store->domain)->first();

        if ($suppliers->isEmpty() || $materials->isEmpty()) {
            return;
        }

        // Create 10-15 purchase orders for each store
        $orderCount = rand(10, 15);

        for ($i = 1; $i <= $orderCount; $i++) {
            $supplier = $suppliers->random();
            $this->createPurchaseOrder($store, $supplier, $materials, $createdBy, $i);
        }
    }

    private function createPurchaseOrder(Store $store, MaterialSupplier $supplier, $materials, ?User $createdBy, int $orderNumber): void
    {
        $orderDate = Carbon::now()->subDays(rand(1, 90));
        $expectedDeliveryDate = $orderDate->copy()->addDays(rand(7, 30));

        // Generate PO number
        $poNumber = 'PO-' . $store->slug . '-' . $orderDate->format('Ymd') . '-' . str_pad($orderNumber, 3, '0', STR_PAD_LEFT);

        // Determine status based on order age
        $daysSinceOrder = $orderDate->diffInDays(Carbon::now());
        $status = $this->determineOrderStatus($daysSinceOrder, $expectedDeliveryDate);

        $purchaseOrder = MaterialPurchaseOrder::create([
            'store_id' => $store->id,
            'supplier_id' => $supplier->id,
            'po_number' => $poNumber,
            'supplier_reference' => 'SUP-REF-' . rand(1000, 9999),
            'order_date' => $orderDate,
            'expected_delivery_date' => $expectedDeliveryDate,
            'actual_delivery_date' => $status === 'received' ? $expectedDeliveryDate->copy()->addDays(rand(-2, 5)) : null,
            'priority' => $this->getRandomPriority(),
            'status' => $status,
            'subtotal' => 0, // Will be calculated after items
            'tax_amount' => 0,
            'discount_amount' => rand(0, 1) == 1 ? rand(100000, 500000) : 0,
            'shipping_cost' => rand(50000, 200000),
            'other_charges' => rand(0, 1) == 1 ? rand(20000, 100000) : 0,
            'total_amount' => 0, // Will be calculated after items
            'currency' => 'VND',
            'payment_terms' => $supplier->payment_terms,
            'payment_due_date' => $orderDate->copy()->addDays($this->getPaymentTermsDays($supplier->payment_terms)),
            'payment_status' => $this->getPaymentStatus($status),
            'delivery_address' => $store->address,
            'delivery_contact' => 'Quản lý kho',
            'delivery_phone' => $store->phone,
            'delivery_instructions' => $this->getRandomDeliveryInstructions(),
            'delivery_method' => $this->getRandomDeliveryMethod(),
            'tracking_number' => $status === 'confirmed' || $status === 'received' ? 'TRK-' . rand(100000, 999999) : null,
            'requested_by' => $createdBy?->id,
            'approved_by' => $status !== 'draft' ? $createdBy?->id : null,
            'approved_at' => $status !== 'draft' ? $orderDate->copy()->addHours(rand(1, 24)) : null,
            'notes' => $this->getRandomOrderNotes(),
        ]);

        // Create order items
        $this->createOrderItems($purchaseOrder, $materials);

        // Update totals
        $this->updateOrderTotals($purchaseOrder);
    }

    private function createOrderItems(MaterialPurchaseOrder $purchaseOrder, $materials): void
    {
        $itemCount = rand(3, min(8, $materials->count())); // 3-8 items per order, but not more than available
        $selectedMaterials = $materials->random($itemCount);

        foreach ($selectedMaterials as $material) {
            $quantity = $this->getRandomQuantity($material);
            $unitPrice = rand(50000, 1000000);
            $lineTotal = $quantity * $unitPrice;
            $discount = rand(0, 1) == 1 ? $lineTotal * (rand(5, 15) / 100) : 0;
            $netAmount = $lineTotal - $discount;

            MaterialPurchaseOrderItem::create([
                'purchase_order_id' => $purchaseOrder->id,
                'material_id' => $material->id,
                'material_code' => $material->material_code,
                'material_name' => $material->name,
                'material_description' => $material->description ?? $material->name,
                'quantity_ordered' => $quantity,
                'quantity_received' => $purchaseOrder->status === 'received' ? $quantity :
                    ($purchaseOrder->status === 'confirmed' ? $quantity * 0.8 : 0),
                'quantity_pending' => $purchaseOrder->status === 'received' ? 0 :
                    ($purchaseOrder->status === 'confirmed' ? $quantity * 0.2 : $quantity),
                'unit' => $material->primaryUnit->symbol ?? 'pcs',
                'unit_price' => $unitPrice,
                'discount_percent' => $discount > 0 ? round(($discount / $lineTotal) * 100, 2) : 0,
                'discount_amount' => $discount,
                'tax_percent' => 10,
                'tax_amount' => $netAmount * 0.1,
                'line_total' => $netAmount * 1.1,
                'expected_delivery_date' => $purchaseOrder->expected_delivery_date,
                'specifications' => json_encode([
                    'quality_grade' => $this->getRandomQualityGrade(),
                    'packaging' => $this->getRandomPackaging(),
                    'special_requirements' => $this->getRandomSpecialRequirements(),
                ]),
            ]);
        }
    }

    private function updateOrderTotals(MaterialPurchaseOrder $purchaseOrder): void
    {
        $items = $purchaseOrder->items;
        $subtotal = $items->sum('line_total');
        $taxAmount = $items->sum('tax_amount');
        $totalAmount = $subtotal + $taxAmount + $purchaseOrder->shipping_cost + $purchaseOrder->other_charges - $purchaseOrder->discount_amount;

        $purchaseOrder->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    private function determineOrderStatus(int $daysSinceOrder, Carbon $expectedDeliveryDate): string
    {
        if ($daysSinceOrder < 1) return 'draft';
        if ($daysSinceOrder < 2) return 'pending';
        if ($daysSinceOrder < 3) return 'approved';
        if ($expectedDeliveryDate->isFuture()) return 'sent';
        if ($expectedDeliveryDate->isToday() || $expectedDeliveryDate->isYesterday()) return 'confirmed';
        return 'received';
    }

    private function getRandomPriority(): string
    {
        $priorities = ['low', 'normal', 'high', 'urgent'];
        $weights = [20, 50, 25, 5]; // Weighted random
        return $priorities[array_search(max($weights), $weights)];
    }

    private function getPaymentStatus(string $orderStatus): string
    {
        if ($orderStatus === 'draft') return 'pending';
        if ($orderStatus === 'delivered') return rand(0, 1) == 1 ? 'paid' : 'partial';
        return 'pending';
    }

    private function getRandomPaymentMethod(): string
    {
        $methods = ['bank_transfer', 'cash', 'check', 'credit'];
        return $methods[array_rand($methods)];
    }

    private function getPaymentTermsDays(string $paymentTerms): int
    {
        return match($paymentTerms) {
            'net_15' => 15,
            'net_30' => 30,
            'net_45' => 45,
            'net_60' => 60,
            default => 30,
        };
    }

    private function getRandomDeliveryMethod(): string
    {
        $methods = ['pickup', 'delivery', 'shipping'];
        return $methods[array_rand($methods)];
    }

    private function getRandomDeliveryInstructions(): string
    {
        $instructions = [
            'Giao hàng trong giờ hành chính',
            'Liên hệ trước khi giao 30 phút',
            'Giao tại kho chính, cổng số 1',
            'Cần xe cẩu để dỡ hàng',
            'Kiểm tra chất lượng trước khi nhận',
        ];
        return $instructions[array_rand($instructions)];
    }

    private function getRandomOrderNotes(): string
    {
        $notes = [
            'Đơn hàng khẩn cấp cho dự án',
            'Yêu cầu chất lượng cao',
            'Giao hàng đúng hạn',
            'Kiểm tra kỹ trước khi giao',
            'Đơn hàng thường xuyên',
        ];
        return $notes[array_rand($notes)];
    }

    private function getPriorityReason(): string
    {
        $reasons = [
            'Dự án khẩn cấp',
            'Hết hàng trong kho',
            'Đơn hàng khách hàng VIP',
            'Mùa cao điểm',
            'Đơn hàng thường xuyên',
        ];
        return $reasons[array_rand($reasons)];
    }

    private function getRandomQuantity(BuildingMaterial $material): int
    {
        $categoryName = $material->category->name ?? '';
        
        if (str_contains(strtolower($categoryName), 'thép')) {
            return rand(10, 100);
        } elseif (str_contains(strtolower($categoryName), 'xi măng')) {
            return rand(50, 300);
        } elseif (str_contains(strtolower($categoryName), 'gạch')) {
            return rand(100, 1000);
        } else {
            return rand(20, 200);
        }
    }

    private function getRandomItemNotes(BuildingMaterial $material): string
    {
        $notes = [
            'Chất lượng theo tiêu chuẩn TCVN',
            'Kiểm tra kỹ trước khi nhận',
            'Đóng gói cẩn thận',
            'Hàng mới sản xuất',
            'Theo đúng specifications',
        ];
        return $notes[array_rand($notes)];
    }

    private function getRandomQualityGrade(): string
    {
        $grades = ['A', 'B', 'Premium', 'Standard'];
        return $grades[array_rand($grades)];
    }

    private function getRandomPackaging(): string
    {
        $packaging = ['Pallet', 'Bundle', 'Box', 'Bag', 'Loose'];
        return $packaging[array_rand($packaging)];
    }

    private function getRandomSpecialRequirements(): string
    {
        $requirements = [
            'Không yêu cầu đặc biệt',
            'Bảo quản khô ráo',
            'Tránh va đập',
            'Kiểm tra chất lượng 100%',
            'Giao hàng cẩn thận',
        ];
        return $requirements[array_rand($requirements)];
    }

    private function calculatePaidAmount(string $paymentStatus, float $totalAmount): float
    {
        return match($paymentStatus) {
            'paid' => $totalAmount,
            'partial' => $totalAmount * 0.5,
            default => 0,
        };
    }
}
