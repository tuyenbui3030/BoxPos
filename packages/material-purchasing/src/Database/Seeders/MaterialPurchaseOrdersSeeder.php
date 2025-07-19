<?php

namespace Packages\MaterialPurchasing\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\MaterialPurchasing\Models\MaterialPurchaseOrder;
use Packages\MaterialSuppliers\Models\MaterialSupplier;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class MaterialPurchaseOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = Store::active()->get();

        foreach ($stores as $store) {
            $this->createPurchaseOrdersForStore($store->id);
        }
    }

    /**
     * Create purchase orders for a specific store.
     */
    private function createPurchaseOrdersForStore(int $storeId): void
    {
        $suppliers = MaterialSupplier::where('store_id', $storeId)->get();
        $users = User::whereHas('stores', function($q) use ($storeId) {
            $q->where('store_id', $storeId);
        })->get();

        $statuses = ['draft', 'pending', 'approved', 'sent', 'confirmed', 'partial_received', 'received', 'closed'];
        $priorities = ['low', 'normal', 'high', 'urgent'];
        $paymentTerms = ['cash', 'cod', 'net_15', 'net_30', 'net_60'];

        // Create 10 purchase orders per store
        for ($i = 1; $i <= 10; $i++) {
            $supplier = $suppliers->random();
            $requestedBy = $users->random();
            $approvedBy = $users->random();
            
            $orderDate = now()->subDays(rand(1, 90));
            $status = $statuses[array_rand($statuses)];
            $priority = $priorities[array_rand($priorities)];
            
            // Generate realistic amounts
            $subtotal = rand(5000000, 50000000); // 5M - 50M VND
            $taxRate = 0.1; // 10% VAT
            $taxAmount = $subtotal * $taxRate;
            $discountAmount = rand(0, $subtotal * 0.05); // 0-5% discount
            $shippingCost = rand(100000, 1000000); // 100K - 1M VND
            $totalAmount = $subtotal + $taxAmount - $discountAmount + $shippingCost;

            MaterialPurchaseOrder::create([
                'store_id' => $storeId,
                'supplier_id' => $supplier->id,
                'po_number' => $this->generatePONumber($storeId, $i),
                'supplier_reference' => 'SUP-REF-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                
                // Order details
                'order_date' => $orderDate,
                'expected_delivery_date' => $orderDate->copy()->addDays(rand(7, 30)),
                'actual_delivery_date' => in_array($status, ['received', 'closed']) ? 
                    $orderDate->copy()->addDays(rand(5, 25)) : null,
                'priority' => $priority,
                'status' => $status,
                
                // Financial details
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'shipping_cost' => $shippingCost,
                'other_charges' => 0,
                'total_amount' => $totalAmount,
                'currency' => 'VND',
                
                // Payment terms
                'payment_terms' => $paymentTerms[array_rand($paymentTerms)],
                'payment_due_date' => $orderDate->copy()->addDays(30),
                'payment_status' => $this->getPaymentStatus($status),
                
                // Delivery details
                'delivery_address' => $this->generateDeliveryAddress($storeId),
                'delivery_contact' => 'Nguyễn Văn ' . chr(65 + $i),
                'delivery_phone' => '090' . rand(1000000, 9999999),
                'delivery_instructions' => 'Giao hàng trong giờ hành chính, liên hệ trước 30 phút',
                'delivery_method' => rand(1, 10) > 7 ? 'pickup' : 'delivery',
                'tracking_number' => in_array($status, ['sent', 'confirmed', 'partial_received', 'received']) ? 
                    'TRK' . date('Ymd') . str_pad($i, 4, '0', STR_PAD_LEFT) : null,
                
                // Approval workflow
                'requested_by' => $requestedBy->id,
                'approved_by' => in_array($status, ['approved', 'sent', 'confirmed', 'partial_received', 'received', 'closed']) ? 
                    $approvedBy->id : null,
                'approved_at' => in_array($status, ['approved', 'sent', 'confirmed', 'partial_received', 'received', 'closed']) ? 
                    $orderDate->copy()->addHours(rand(1, 48)) : null,
                'approval_notes' => in_array($status, ['approved', 'sent', 'confirmed', 'partial_received', 'received', 'closed']) ? 
                    'Đã duyệt đơn hàng theo quy trình' : null,
                
                // Additional information
                'notes' => $this->generateOrderNotes($supplier, $priority),
                'terms_conditions' => 'Áp dụng điều khoản tiêu chuẩn của công ty',
                'attachments' => null,
                'is_recurring' => rand(1, 10) > 8,
                'recurring_frequency' => rand(1, 10) > 8 ? 'monthly' : null,
            ]);
        }
    }

    /**
     * Generate PO number.
     */
    private function generatePONumber(int $storeId, int $sequence): string
    {
        $year = date('Y');
        $month = date('m');
        return "PO{$year}{$month}{$storeId}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get payment status based on order status.
     */
    private function getPaymentStatus(string $orderStatus): string
    {
        switch ($orderStatus) {
            case 'draft':
            case 'pending':
            case 'approved':
            case 'sent':
                return 'pending';
            case 'confirmed':
            case 'partial_received':
                return rand(1, 10) > 7 ? 'partial' : 'pending';
            case 'received':
            case 'closed':
                return rand(1, 10) > 8 ? 'paid' : (rand(1, 10) > 6 ? 'partial' : 'pending');
            default:
                return 'pending';
        }
    }

    /**
     * Generate delivery address.
     */
    private function generateDeliveryAddress(int $storeId): string
    {
        $addresses = [
            'Kho vật liệu xây dựng, 123 Đường Nguyễn Huệ, Quận 1, TP.HCM',
            'Công trình xây dựng ABC, 456 Đường Lê Lợi, Quận 3, TP.HCM',
            'Nhà máy sản xuất XYZ, 789 Đường Trần Hưng Đạo, Quận 5, TP.HCM',
            'Dự án căn hộ DEF, 321 Đường Võ Văn Kiệt, Quận 6, TP.HCM',
            'Khu công nghiệp GHI, 654 Đường Nguyễn Văn Cừ, Quận 8, TP.HCM',
        ];
        
        return $addresses[array_rand($addresses)];
    }

    /**
     * Generate order notes.
     */
    private function generateOrderNotes(MaterialSupplier $supplier, string $priority): string
    {
        $notes = [
            "Đơn hàng từ {$supplier->company_name}",
            "Ưu tiên: " . ucfirst($priority),
        ];

        if ($priority === 'urgent') {
            $notes[] = "Cần giao hàng gấp cho dự án";
        }

        if ($supplier->is_preferred) {
            $notes[] = "Nhà cung cấp ưu tiên";
        }

        return implode('. ', $notes);
    }
}
