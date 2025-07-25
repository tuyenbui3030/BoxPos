<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\Payments\Models\PaymentMethod;
use Packages\Payments\Models\Payment;
use Packages\SalesOrders\Models\SalesOrder;
use Packages\User\Models\User;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('💳 Seeding Payments...');

        $stores = Store::all();

        foreach ($stores as $store) {
            $this->createPaymentsForStore($store);
        }

        $this->command->info('✅ Payments seeded successfully!');
    }

    private function createPaymentsForStore(Store $store): void
    {
        $createdBy = User::where('email', 'admin@' . strtolower($store->slug) . '.boxpos.vn')->first();

        // Create payment methods
        $paymentMethods = $this->createPaymentMethods($store, $createdBy);
        
        // Create payments for sales orders
        $this->createPaymentsForSalesOrders($store, $paymentMethods, $createdBy);
    }

    private function createPaymentMethods(Store $store, ?User $createdBy): array
    {
        $methods = [
            [
                'name' => 'Tiền mặt',
                'code' => 'CASH',
                'type' => 'cash',
                'description' => 'Thanh toán bằng tiền mặt',
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
                'currency' => 'VND',
                'supports_refund' => true,
                'supports_partial_refund' => true,
            ],
            [
                'name' => 'Chuyển khoản ngân hàng',
                'code' => 'BANK_TRANSFER',
                'type' => 'bank_transfer',
                'description' => 'Chuyển khoản qua ngân hàng',
                'is_active' => true,
                'sort_order' => 2,
                'currency' => 'VND',
                'supports_refund' => true,
                'supports_partial_refund' => true,
                'settlement_days' => 1,
            ],
            [
                'name' => 'Thẻ tín dụng/ghi nợ',
                'code' => 'CARD',
                'type' => 'card',
                'description' => 'Thanh toán bằng thẻ tín dụng hoặc ghi nợ',
                'is_active' => true,
                'sort_order' => 3,
                'currency' => 'VND',
                'supports_refund' => true,
                'supports_partial_refund' => true,
                'processing_fee_percent' => 2.5,
                'requires_verification' => true,
            ],
            [
                'name' => 'Ví điện tử',
                'code' => 'E_WALLET',
                'type' => 'e_wallet',
                'description' => 'Thanh toán qua ví điện tử (Momo, ZaloPay, etc.)',
                'is_active' => true,
                'sort_order' => 4,
                'currency' => 'VND',
                'supports_refund' => true,
                'supports_partial_refund' => false,
                'processing_fee_percent' => 1.5,
            ],
        ];

        $createdMethods = [];
        foreach ($methods as $methodData) {
            $method = PaymentMethod::create([
                ...$methodData,
                'store_id' => $store->id,
                'created_by' => $createdBy?->id,
            ]);
            $createdMethods[] = $method;
        }

        return $createdMethods;
    }

    private function createPaymentsForSalesOrders(Store $store, array $paymentMethods, ?User $createdBy): void
    {
        $salesOrders = SalesOrder::where('store_id', $store->id)
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->get();

        foreach ($salesOrders as $salesOrder) {
            // 80% chance to create payment for each sales order
            if (rand(1, 100) <= 80) {
                $this->createPaymentForSalesOrder($salesOrder, $paymentMethods, $createdBy);
            }
        }
    }

    private function createPaymentForSalesOrder(SalesOrder $salesOrder, array $paymentMethods, ?User $createdBy): void
    {
        $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
        $paymentDate = Carbon::parse($salesOrder->order_date)->addDays(rand(0, 7));
        
        // Determine payment amount (full or partial)
        $paymentAmount = $this->getPaymentAmount($salesOrder->total_amount);
        
        $processingFee = $paymentMethod->calculateProcessingFee($paymentAmount);
        $netAmount = $paymentAmount - $processingFee;

        Payment::create([
            'store_id' => $salesOrder->store_id,
            'payment_method_id' => $paymentMethod->id,
            'payment_number' => $this->generatePaymentNumber($paymentDate),
            'order_number' => $salesOrder->order_number,
            'order_id' => $salesOrder->id,
            'order_type' => 'sales_order',
            'customer_id' => $salesOrder->customer_id,
            'payment_date' => $paymentDate,
            'amount' => $paymentAmount,
            'currency' => $salesOrder->currency ?? 'VND',
            'exchange_rate' => 1.0,
            'base_amount' => $paymentAmount, // Same as amount since same currency
            'processing_fee' => $processingFee,
            'net_amount' => $netAmount,
            'status' => $this->getPaymentStatus($paymentDate),
            'verification_status' => $paymentMethod->requires_verification ? 'verified' : 'not_required',
            'processed_at' => $paymentDate->copy()->addMinutes(rand(5, 60)),
            'verified_at' => $paymentMethod->requires_verification ? $paymentDate->copy()->addMinutes(rand(10, 120)) : null,
            'verified_by' => $paymentMethod->requires_verification ? $createdBy?->id : null,
            'provider_reference' => $this->generateReferenceNumber($paymentMethod),
            'provider_transaction_id' => $this->generateTransactionId($paymentMethod),
            'provider_response' => $this->getGatewayResponse($paymentMethod),
            'notes' => $this->getPaymentNotes($paymentMethod),
            'processed_by' => $createdBy?->id,
            'metadata' => json_encode([
                'created_via' => 'seeder',
                'payment_channel' => $this->getPaymentChannel($paymentMethod),
            ]),
        ]);
    }

    private function generatePaymentNumber(Carbon $date): string
    {
        return 'PAY-' . $date->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function getPaymentAmount(float $totalAmount): float
    {
        // 70% full payment, 30% partial payment
        if (rand(1, 100) <= 70) {
            return $totalAmount; // Full payment
        } else {
            return $totalAmount * (rand(30, 80) / 100); // Partial payment
        }
    }

    private function getPaymentStatus(Carbon $paymentDate): string
    {
        $daysSince = $paymentDate->diffInDays(Carbon::now());
        
        if ($daysSince < 1) {
            return 'pending';
        } else {
            return rand(0, 1) == 1 ? 'completed' : 'processing';
        }
    }

    private function getPaymentType(float $paymentAmount, float $totalAmount): string
    {
        return $paymentAmount >= $totalAmount ? 'full' : 'partial';
    }

    private function generateReferenceNumber(PaymentMethod $paymentMethod): ?string
    {
        return match($paymentMethod->type) {
            'bank_transfer' => 'BT-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'card' => 'CD-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'e_wallet' => 'EW-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
            default => null,
        };
    }

    private function generateTransactionId(PaymentMethod $paymentMethod): ?string
    {
        if ($paymentMethod->type === 'cash') {
            return null;
        }
        
        return 'TXN-' . strtoupper(substr($paymentMethod->code, 0, 3)) . '-' .
               str_pad(rand(1, 9999999), 7, '0', STR_PAD_LEFT);
    }

    private function getGatewayResponse(PaymentMethod $paymentMethod): ?string
    {
        if ($paymentMethod->type === 'cash') {
            return null;
        }

        return json_encode([
            'status' => 'success',
            'message' => 'Payment processed successfully',
            'gateway' => $paymentMethod->type,
            'response_code' => '00',
        ]);
    }

    private function getPaymentNotes(PaymentMethod $paymentMethod): ?string
    {
        $notes = [
            'cash' => 'Thanh toán bằng tiền mặt tại quầy',
            'bank_transfer' => 'Chuyển khoản qua ngân hàng',
            'card' => 'Thanh toán bằng thẻ tín dụng/ghi nợ',
            'e_wallet' => 'Thanh toán qua ví điện tử',
        ];
        
        return $notes[$paymentMethod->type] ?? null;
    }

    private function getPaymentChannel(PaymentMethod $paymentMethod): string
    {
        return match($paymentMethod->type) {
            'cash' => 'pos',
            'bank_transfer' => 'online_banking',
            'card' => 'pos_terminal',
            'e_wallet' => 'mobile_app',
            default => 'manual',
        };
    }
}
