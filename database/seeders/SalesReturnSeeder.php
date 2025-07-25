<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\SalesOrders\Models\SalesOrder;
use Packages\SalesOrders\Models\SalesReturn;
use Packages\SalesOrders\Models\SalesReturnItem;
use Packages\User\Models\User;
use Carbon\Carbon;

class SalesReturnSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('↩️ Seeding Sales Returns...');

        $stores = Store::all();

        foreach ($stores as $store) {
            $this->createSalesReturnsForStore($store);
        }

        $this->command->info('✅ Sales Returns seeded successfully!');
    }

    private function createSalesReturnsForStore(Store $store): void
    {
        $salesOrders = SalesOrder::where('store_id', $store->id)
            ->whereIn('status', ['delivered'])
            ->where('order_date', '<=', Carbon::now()->subDays(7)) // At least 7 days old
            ->get();

        if ($salesOrders->isEmpty()) {
            return;
        }

        $createdBy = User::where('email', 'admin@' . $store->domain)->first();

        foreach ($salesOrders as $salesOrder) {
            // 15% chance to create return for each delivered sales order
            if (rand(1, 100) <= 15) {
                $this->createSalesReturnForOrder($salesOrder, $createdBy);
            }
        }
    }

    private function createSalesReturnForOrder(SalesOrder $salesOrder, ?User $createdBy): void
    {
        $returnDate = $this->getReturnDate($salesOrder);
        $returnNumber = $this->generateReturnNumber($salesOrder, $returnDate);
        $returnReason = $this->getRandomReturnReason();
        
        $salesReturn = SalesReturn::create([
            'store_id' => $salesOrder->store_id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $salesOrder->customer_id,
            'return_number' => $returnNumber,
            'return_date' => $returnDate,
            'return_type' => $this->getReturnType($returnReason),
            'return_reason' => $returnReason,
            'status' => $this->getReturnStatus($returnDate),
            'subtotal' => 0, // Will be calculated after items
            'tax_amount' => 0,
            'total_amount' => 0, // Will be calculated after items
            'currency' => $salesOrder->currency,
            'refund_method' => $this->getRefundMethod($salesOrder),
            'refund_status' => $this->getRefundStatus($returnDate),
            'refund_amount' => 0, // Will be calculated after processing
            'restocking_fee' => $this->getRestockingFee($returnReason),
            'restock_items' => true,
            'item_condition' => $this->getItemCondition($returnReason),
            'processed_by' => $createdBy?->id,
            'approved_by' => $createdBy?->id,
            'approved_at' => $returnDate->copy()->addHours(rand(4, 12)),
            'metadata' => json_encode([
                'created_via' => 'seeder',
                'original_order_date' => $salesOrder->order_date,
                'days_since_purchase' => Carbon::parse($salesOrder->order_date)->diffInDays($returnDate),
            ]),
        ]);

        // Create return items
        $this->createReturnItems($salesReturn, $salesOrder);

        // Update return totals
        $this->updateReturnTotals($salesReturn);
    }

    private function createReturnItems(SalesReturn $salesReturn, SalesOrder $salesOrder): void
    {
        $orderItems = $salesOrder->items;
        
        // Return 1-3 items from the order
        $itemsToReturn = $orderItems->random(rand(1, min(3, $orderItems->count())));

        foreach ($itemsToReturn as $orderItem) {
            $quantityToReturn = $this->getQuantityToReturn($orderItem);
            
            if ($quantityToReturn <= 0) {
                continue;
            }

            $unitPrice = $orderItem->unit_price;
            $lineTotal = $quantityToReturn * $unitPrice;
            $discountAmount = $lineTotal * ($orderItem->discount_percentage / 100);
            $netAmount = $lineTotal - $discountAmount;
            $taxAmount = $netAmount * ($orderItem->tax_rate / 100);
            $totalAmount = $netAmount + $taxAmount;

            SalesReturnItem::create([
                'sales_return_id' => $salesReturn->id,
                'sales_order_item_id' => $orderItem->id,
                'item_type' => $orderItem->item_type,
                'item_id' => $orderItem->item_id,
                'item_code' => $orderItem->item_code,
                'item_name' => $orderItem->item_name,
                'item_description' => $orderItem->notes,
                'original_quantity' => $orderItem->quantity,
                'return_quantity' => $quantityToReturn,
                'unit' => $orderItem->unit,
                'original_unit_price' => $orderItem->unit_price,
                'return_unit_price' => $unitPrice,
                'line_total' => $totalAmount,
                'return_reason' => $salesReturn->return_reason,
                'item_condition' => $salesReturn->item_condition,
                'can_restock' => $salesReturn->item_condition !== 'damaged' && $salesReturn->item_condition !== 'defective',
                'notes' => $this->generateReturnItemNotes($orderItem, $quantityToReturn, $salesReturn->return_reason),
                'metadata' => json_encode([
                    'original_quantity' => $orderItem->quantity,
                    'return_percentage' => round(($quantityToReturn / $orderItem->quantity) * 100, 2),
                ]),
            ]);
        }
    }

    private function updateReturnTotals(SalesReturn $salesReturn): void
    {
        $items = $salesReturn->items;
        $subtotal = $items->sum('net_amount');
        $taxAmount = $items->sum('tax_amount');
        $totalAmount = $subtotal + $taxAmount - $salesReturn->restocking_fee;

        $refundAmount = $this->calculateRefundAmount($salesReturn->refund_status, $totalAmount);

        $salesReturn->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'refund_amount' => $refundAmount,
        ]);
    }

    private function getReturnDate(SalesOrder $salesOrder): Carbon
    {
        // Return date is 7-30 days after order date
        $orderDate = Carbon::parse($salesOrder->order_date);
        return $orderDate->addDays(rand(7, 30));
    }

    private function generateReturnNumber(SalesOrder $salesOrder, Carbon $returnDate): string
    {
        $storeCode = strtoupper(substr($salesOrder->store->slug, 0, 3));
        $dateCode = $returnDate->format('Ymd');
        $sequence = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return "RET-{$storeCode}-{$dateCode}-{$sequence}";
    }

    private function getRandomReturnReason(): string
    {
        // Available reasons: 'defective', 'wrong_item', 'customer_change_mind', 'damaged_shipping', 'expired', 'other'
        $reasons = [
            'defective',
            'wrong_item',
            'customer_change_mind',
            'damaged_shipping',
            'expired',
            'other',
        ];

        return $reasons[array_rand($reasons)];
    }

    private function getReturnType(string $returnReason): string
    {
        // Available types: 'full_return', 'partial_return', 'exchange', 'warranty'
        if (in_array($returnReason, ['defective', 'damaged_shipping'])) {
            return 'warranty';
        } elseif ($returnReason === 'wrong_item') {
            return 'exchange';
        } else {
            return rand(0, 1) == 1 ? 'full_return' : 'partial_return';
        }
    }

    private function getReturnStatus(Carbon $returnDate): string
    {
        // Available statuses: 'pending', 'approved', 'rejected', 'processed', 'refunded', 'exchanged'
        $daysSinceReturn = $returnDate->diffInDays(Carbon::now());

        if ($daysSinceReturn < 1) {
            return 'pending';
        } elseif ($daysSinceReturn < 3) {
            return 'approved';
        } else {
            $rand = rand(1, 100);
            if ($rand <= 60) return 'processed';
            if ($rand <= 80) return 'refunded';
            if ($rand <= 95) return 'exchanged';
            return 'rejected';
        }
    }

    private function getRefundMethod(SalesOrder $salesOrder): string
    {
        // Available methods: 'cash', 'card', 'bank_transfer', 'store_credit', 'exchange'
        // Match original payment method when possible
        if ($salesOrder->payment_method === 'cash') {
            return 'cash';
        } elseif ($salesOrder->payment_method === 'card') {
            return 'card';
        } else {
            $rand = rand(1, 100);
            if ($rand <= 40) return 'bank_transfer';
            if ($rand <= 70) return 'store_credit';
            return 'exchange';
        }
    }

    private function getRefundStatus(Carbon $returnDate): string
    {
        // Available statuses: 'pending', 'approved', 'processed', 'completed', 'rejected'
        $daysSinceReturn = $returnDate->diffInDays(Carbon::now());

        if ($daysSinceReturn < 2) {
            return 'pending';
        } elseif ($daysSinceReturn < 5) {
            return 'approved';
        } else {
            $rand = rand(1, 100);
            if ($rand <= 70) return 'completed';
            if ($rand <= 90) return 'processed';
            return 'rejected';
        }
    }

    private function getRestockingFee(string $returnReason): float
    {
        // Restocking fee for customer change of mind
        if ($returnReason === 'customer_change_mind') {
            return rand(50000, 200000); // 50k - 200k VND
        }

        return 0;
    }

    private function getItemCondition(string $returnReason): string
    {
        // Available conditions: 'new', 'good', 'fair', 'damaged', 'defective'
        if (in_array($returnReason, ['defective', 'damaged_shipping'])) {
            return 'damaged';
        } elseif ($returnReason === 'expired') {
            return 'defective';
        } else {
            return rand(0, 1) == 1 ? 'good' : 'fair';
        }
    }

    private function getQualityCheckStatus(Carbon $returnDate): string
    {
        $daysSinceReturn = $returnDate->diffInDays(Carbon::now());
        
        if ($daysSinceReturn < 1) {
            return 'pending';
        } else {
            return 'completed';
        }
    }

    private function getQualityCheckNotes(string $returnReason): ?string
    {
        $notes = [
            'Sản phẩm bị lỗi' => 'Xác nhận sản phẩm có lỗi kỹ thuật',
            'Hư hỏng trong vận chuyển' => 'Hư hỏng do vận chuyển, không phải lỗi sản xuất',
            'Chất lượng không đạt' => 'Chất lượng không đạt tiêu chuẩn cam kết',
            'Không đúng mô tả' => 'Sản phẩm không khớp với mô tả',
        ];
        
        return $notes[$returnReason] ?? 'Sản phẩm trong tình trạng bình thường';
    }

    private function getCustomerNotes(string $returnReason): ?string
    {
        $notes = [
            'Sản phẩm bị lỗi' => 'Sản phẩm không hoạt động đúng cách',
            'Khách hàng đổi ý' => 'Không còn nhu cầu sử dụng',
            'Giao sai sản phẩm' => 'Nhận được sản phẩm khác với đơn hàng',
            'Hư hỏng trong vận chuyển' => 'Sản phẩm bị hư hỏng khi nhận hàng',
        ];
        
        return $notes[$returnReason] ?? null;
    }

    private function getQuantityToReturn($orderItem): float
    {
        // Return 30-100% of ordered quantity
        $percentage = rand(30, 100) / 100;
        return floor($orderItem->quantity * $percentage);
    }

    private function getQuantityRestocked(float $quantityReturned, string $conditionAssessment): float
    {
        if ($conditionAssessment === 'good') {
            return $quantityReturned; // 100% restocked
        } elseif ($conditionAssessment === 'fair') {
            return floor($quantityReturned * 0.8); // 80% restocked
        } else {
            return 0; // Cannot restock damaged items
        }
    }

    private function getQuantityDamaged(float $quantityReturned, string $conditionAssessment): float
    {
        if ($conditionAssessment === 'damaged') {
            return $quantityReturned; // All damaged
        } elseif ($conditionAssessment === 'defective') {
            return floor($quantityReturned * 0.5); // 50% damaged
        } else {
            return 0; // No damage
        }
    }

    private function getItemReturnReason(string $returnReason): string
    {
        return $returnReason;
    }

    private function generateReturnItemNotes($orderItem, float $quantityReturned, string $returnReason): ?string
    {
        $notes = [];
        
        if ($quantityReturned < $orderItem->quantity) {
            $notes[] = "Trả một phần: {$quantityReturned}/{$orderItem->quantity}";
        }
        
        $notes[] = "Lý do: {$returnReason}";
        
        return implode('. ', $notes);
    }

    private function calculateRefundAmount(string $refundStatus, float $totalAmount): float
    {
        return match($refundStatus) {
            'completed' => $totalAmount,
            'processing' => $totalAmount * 0.5, // Partial refund processed
            default => 0,
        };
    }
}
