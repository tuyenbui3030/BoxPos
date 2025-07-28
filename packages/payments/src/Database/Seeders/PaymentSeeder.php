<?php

namespace Packages\Payments\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Illuminate\Support\Facades\DB;
use Packages\Payments\Models\Payment;
use Packages\Payments\Models\PaymentMethod;
use Packages\SalesOrders\Models\Invoice;
use Packages\Customer\Models\Customer;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Carbon\Carbon;

class PaymentSeeder extends BasePackageSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensureSeedingAllowed();
        
        $this->executeWithTransaction(function () {
            $this->seedPaymentMethods();
            $this->seedPayments();
        });
    }

    /**
     * Seed payment methods for each store
     */
    private function seedPaymentMethods(): void
    {
        $this->logSeedingProgress('seeding_payment_methods_started');

        $stores = $this->getStores();
        
        foreach ($stores as $store) {
            $this->logSeedingProgress('seeding_payment_methods_for_store', [
                'store_id' => $store->id,
                'store_name' => $store->name
            ]);

            $adminUser = User::where('email', 'admin@boxpos.com')->first();
            
            // Standard payment methods for each store
            $paymentMethods = [
                [
                    'code' => 'CASH',
                    'name' => 'Tiền mặt',
                    'description' => 'Thanh toán bằng tiền mặt tại quầy',
                    'type' => 'cash',
                    'category' => 'instant',
                    'requires_verification' => false,
                    'provider' => 'internal',
                    'provider_code' => 'CASH',
                    'processing_fee_percent' => 0.00,
                    'processing_fee_fixed' => 0.00,
                    'min_amount' => 1000.00, // 1K VND
                    'max_amount' => 50000000.00, // 50M VND
                    'currency' => 'VND',
                    'settlement_days' => 0,
                    'processing_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                    'icon' => 'cash',
                    'color' => '#28a745',
                    'show_on_pos' => true,
                    'show_on_website' => true,
                    'show_on_mobile' => true,
                    'sort_order' => 1,
                    'is_active' => true,
                    'is_default' => true,
                    'requires_pin' => false,
                    'requires_signature' => false,
                    'supports_refund' => true,
                    'supports_partial_refund' => true,
                    'refund_days_limit' => 30,
                ],
                [
                    'code' => 'BANK_TRANSFER',
                    'name' => 'Chuyển khoản ngân hàng',
                    'description' => 'Thanh toán qua chuyển khoản ngân hàng',
                    'type' => 'bank_transfer',
                    'category' => 'delayed',
                    'requires_verification' => true,
                    'provider' => 'bank',
                    'provider_code' => 'BANK_TRANSFER',
                    'processing_fee_percent' => 0.00,
                    'processing_fee_fixed' => 0.00,
                    'min_amount' => 10000.00, // 10K VND
                    'max_amount' => null, // No limit
                    'currency' => 'VND',
                    'settlement_days' => 1,
                    'cutoff_time' => '15:00:00',
                    'processing_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    'icon' => 'bank',
                    'color' => '#007bff',
                    'show_on_pos' => true,
                    'show_on_website' => true,
                    'show_on_mobile' => true,
                    'sort_order' => 2,
                    'is_active' => true,
                    'is_default' => false,
                    'requires_pin' => false,
                    'requires_signature' => true,
                    'supports_refund' => true,
                    'supports_partial_refund' => true,
                    'refund_days_limit' => 15,
                ],
                [
                    'code' => 'CREDIT_CARD',
                    'name' => 'Thẻ tín dụng',
                    'description' => 'Thanh toán bằng thẻ tín dụng Visa/MasterCard',
                    'type' => 'card',
                    'category' => 'credit',
                    'requires_verification' => true,
                    'provider' => 'visa_mastercard',
                    'provider_code' => 'CREDIT_CARD',
                    'processing_fee_percent' => 2.50,
                    'processing_fee_fixed' => 5000.00, // 5K VND
                    'min_amount' => 50000.00, // 50K VND
                    'max_amount' => 100000000.00, // 100M VND
                    'currency' => 'VND',
                    'settlement_days' => 2,
                    'processing_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    'icon' => 'credit-card',
                    'color' => '#6f42c1',
                    'show_on_pos' => true,
                    'show_on_website' => true,
                    'show_on_mobile' => true,
                    'sort_order' => 3,
                    'is_active' => true,
                    'is_default' => false,
                    'requires_pin' => true,
                    'requires_signature' => true,
                    'supports_refund' => true,
                    'supports_partial_refund' => true,
                    'refund_days_limit' => 7,
                ],
                [
                    'code' => 'DEBIT_CARD',
                    'name' => 'Thẻ ghi nợ',
                    'description' => 'Thanh toán bằng thẻ ghi nợ ATM',
                    'type' => 'card',
                    'category' => 'instant',
                    'requires_verification' => true,
                    'provider' => 'napas',
                    'provider_code' => 'DEBIT_CARD',
                    'processing_fee_percent' => 1.50,
                    'processing_fee_fixed' => 3000.00, // 3K VND
                    'min_amount' => 20000.00, // 20K VND
                    'max_amount' => 50000000.00, // 50M VND
                    'currency' => 'VND',
                    'settlement_days' => 1,
                    'processing_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    'icon' => 'credit-card-alt',
                    'color' => '#17a2b8',
                    'show_on_pos' => true,
                    'show_on_website' => true,
                    'show_on_mobile' => true,
                    'sort_order' => 4,
                    'is_active' => true,
                    'is_default' => false,
                    'requires_pin' => true,
                    'requires_signature' => false,
                    'supports_refund' => true,
                    'supports_partial_refund' => true,
                    'refund_days_limit' => 7,
                ],
            ];

            // Add e-wallet payment methods for development environment
            if ($this->isDevelopment) {
                $eWalletMethods = [
                    [
                        'code' => 'MOMO',
                        'name' => 'Ví MoMo',
                        'description' => 'Thanh toán qua ví điện tử MoMo',
                        'type' => 'e_wallet',
                        'category' => 'instant',
                        'requires_verification' => true,
                        'provider' => 'momo',
                        'provider_code' => 'MOMO',
                        'processing_fee_percent' => 1.00,
                        'processing_fee_fixed' => 0.00,
                        'min_amount' => 10000.00, // 10K VND
                        'max_amount' => 20000000.00, // 20M VND
                        'currency' => 'VND',
                        'settlement_days' => 1,
                        'processing_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                        'icon' => 'mobile-alt',
                        'color' => '#d63384',
                        'show_on_pos' => true,
                        'show_on_website' => true,
                        'show_on_mobile' => true,
                        'sort_order' => 5,
                        'is_active' => true,
                        'is_default' => false,
                        'requires_pin' => false,
                        'requires_signature' => false,
                        'supports_refund' => true,
                        'supports_partial_refund' => true,
                        'refund_days_limit' => 30,
                    ],
                    [
                        'code' => 'ZALOPAY',
                        'name' => 'ZaloPay',
                        'description' => 'Thanh toán qua ví điện tử ZaloPay',
                        'type' => 'e_wallet',
                        'category' => 'instant',
                        'requires_verification' => true,
                        'provider' => 'zalopay',
                        'provider_code' => 'ZALOPAY',
                        'processing_fee_percent' => 1.20,
                        'processing_fee_fixed' => 0.00,
                        'min_amount' => 10000.00, // 10K VND
                        'max_amount' => 15000000.00, // 15M VND
                        'currency' => 'VND',
                        'settlement_days' => 1,
                        'processing_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                        'icon' => 'wallet',
                        'color' => '#0066cc',
                        'show_on_pos' => true,
                        'show_on_website' => true,
                        'show_on_mobile' => true,
                        'sort_order' => 6,
                        'is_active' => true,
                        'is_default' => false,
                        'requires_pin' => false,
                        'requires_signature' => false,
                        'supports_refund' => true,
                        'supports_partial_refund' => true,
                        'refund_days_limit' => 30,
                    ],
                    [
                        'code' => 'VNPAY',
                        'name' => 'VNPay',
                        'description' => 'Thanh toán qua cổng thanh toán VNPay',
                        'type' => 'other',
                        'category' => 'instant',
                        'requires_verification' => true,
                        'provider' => 'vnpay',
                        'provider_code' => 'VNPAY',
                        'processing_fee_percent' => 2.00,
                        'processing_fee_fixed' => 0.00,
                        'min_amount' => 5000.00, // 5K VND
                        'max_amount' => 500000000.00, // 500M VND
                        'currency' => 'VND',
                        'settlement_days' => 1,
                        'processing_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                        'icon' => 'credit-card',
                        'color' => '#ff6600',
                        'show_on_pos' => false,
                        'show_on_website' => true,
                        'show_on_mobile' => true,
                        'sort_order' => 7,
                        'is_active' => true,
                        'is_default' => false,
                        'requires_pin' => false,
                        'requires_signature' => false,
                        'supports_refund' => true,
                        'supports_partial_refund' => true,
                        'refund_days_limit' => 15,
                    ],
                ];

                $paymentMethods = array_merge($paymentMethods, $eWalletMethods);
            }

            foreach ($paymentMethods as $methodData) {
                PaymentMethod::create(array_merge($methodData, [
                    'store_id' => $store->id,
                    'created_by' => $adminUser?->id,
                ]));
            }
        }

        $this->logSeedingProgress('seeding_payment_methods_completed');
    }

    /**
     * Seed payments for invoices with proper relationships
     */
    private function seedPayments(): void
    {
        $this->logSeedingProgress('seeding_payments_started');

        $stores = $this->getStores();
        
        foreach ($stores as $store) {
            $this->logSeedingProgress('seeding_payments_for_store', [
                'store_id' => $store->id,
                'store_name' => $store->name
            ]);

            $this->seedPaymentsForStore($store);
        }

        $this->logSeedingProgress('seeding_payments_completed');
    }

    /**
     * Seed payments for a specific store
     */
    private function seedPaymentsForStore(Store $store): void
    {
        $invoices = Invoice::where('store_id', $store->id)->get();
        $paymentMethods = PaymentMethod::where('store_id', $store->id)->active()->get();
        $adminUser = User::where('email', 'admin@boxpos.com')->first();

        if ($invoices->isEmpty() || $paymentMethods->isEmpty()) {
            $this->logSeedingProgress('skipping_payments_no_data', [
                'store_id' => $store->id,
                'invoices_count' => $invoices->count(),
                'payment_methods_count' => $paymentMethods->count()
            ]);
            return;
        }

        foreach ($invoices as $invoice) {
            $this->seedPaymentsForInvoice($invoice, $paymentMethods, $adminUser);
        }
    }

    /**
     * Seed payments for a specific invoice
     */
    private function seedPaymentsForInvoice(Invoice $invoice, $paymentMethods, ?User $user): void
    {
        // Determine payment strategy based on invoice amount and random factors
        $totalAmount = $invoice->total_amount;
        $remainingAmount = $totalAmount;
        
        // 70% chance of full payment, 20% partial payment, 10% no payment
        $paymentStrategy = $this->determinePaymentStrategy($totalAmount);
        
        if ($paymentStrategy === 'none') {
            // Update invoice to unpaid status
            $invoice->update([
                'payment_status' => 'pending',
                'paid_amount' => 0,
                'balance_due' => $totalAmount
            ]);
            return;
        }

        $paymentCount = $paymentStrategy === 'full' ? 1 : rand(1, 3); // 1-3 partial payments
        $payments = [];

        for ($i = 0; $i < $paymentCount && $remainingAmount > 0; $i++) {
            $paymentMethod = $this->selectPaymentMethod($paymentMethods, $remainingAmount);
            
            // Calculate payment amount
            if ($i === $paymentCount - 1 || $paymentStrategy === 'full') {
                // Last payment or full payment - pay remaining amount
                $paymentAmount = $remainingAmount;
            } else {
                // Partial payment - pay 30-80% of remaining amount
                $percentage = rand(30, 80) / 100;
                $paymentAmount = round($remainingAmount * $percentage, 2);
                
                // Ensure minimum payment amount
                $paymentAmount = max($paymentAmount, min(50000, $remainingAmount));
            }

            // Calculate processing fee
            $processingFee = $paymentMethod->calculateProcessingFee($paymentAmount);
            $netAmount = $paymentAmount - $processingFee;

            // Generate payment date (between invoice date and now)
            $paymentDate = $this->generatePaymentDate($invoice->invoice_date);

            $payment = Payment::create([
                'store_id' => $invoice->store_id,
                'payment_method_id' => $paymentMethod->id,
                'payment_number' => $this->generatePaymentNumber($invoice->store_id),
                'order_number' => $invoice->invoice_number,
                'order_id' => $invoice->id,
                'order_type' => 'invoice',
                'customer_id' => $invoice->customer_id,
                'amount' => $paymentAmount,
                'currency' => $invoice->currency,
                'exchange_rate' => 1.0,
                'base_amount' => $paymentAmount,
                'processing_fee' => $processingFee,
                'net_amount' => $netAmount,
                'status' => $this->determinePaymentStatus($paymentMethod, $paymentAmount),
                'verification_status' => $paymentMethod->requires_verification ? 'verified' : 'not_required',
                'processed_at' => $paymentDate,
                'verified_at' => $paymentMethod->requires_verification ? $paymentDate->addMinutes(rand(5, 30)) : null,
                'verified_by' => $paymentMethod->requires_verification ? $user?->id : null,
                'payment_date' => $paymentDate,
                'provider_reference' => $this->generateReferenceNumber($paymentMethod),
                'provider_transaction_id' => $this->generateExternalTransactionId($paymentMethod),
                'provider_response' => $this->generateGatewayResponse($paymentMethod),
                'notes' => $this->generatePaymentNotes($paymentMethod, $invoice),
                'metadata' => $this->generatePaymentMetadata($paymentMethod, $invoice),
                'processed_by' => $user?->id,
            ]);

            $payments[] = $payment;
            $remainingAmount -= $paymentAmount;
        }

        // Update invoice payment status
        $totalPaid = collect($payments)->sum('amount');
        $balanceDue = $totalAmount - $totalPaid;
        
        $paymentStatus = $this->determineInvoicePaymentStatus($totalPaid, $totalAmount);
        
        $invoice->update([
            'payment_status' => $paymentStatus,
            'paid_amount' => $totalPaid,
            'balance_due' => $balanceDue
        ]);
    }

    /**
     * Determine payment strategy for an invoice
     */
    private function determinePaymentStrategy(float $amount): string
    {
        $random = rand(1, 100);
        
        // Higher amounts are more likely to be paid in installments
        if ($amount > 10000000) { // > 10M VND
            if ($random <= 50) return 'full';
            if ($random <= 80) return 'partial';
            return 'none';
        } elseif ($amount > 1000000) { // > 1M VND
            if ($random <= 70) return 'full';
            if ($random <= 90) return 'partial';
            return 'none';
        } else {
            if ($random <= 85) return 'full';
            if ($random <= 95) return 'partial';
            return 'none';
        }
    }

    /**
     * Select appropriate payment method based on amount
     */
    private function selectPaymentMethod($paymentMethods, float $amount)
    {
        // Filter methods that can handle this amount
        $validMethods = $paymentMethods->filter(function ($method) use ($amount) {
            return $method->isAmountValid($amount);
        });

        if ($validMethods->isEmpty()) {
            return $paymentMethods->first(); // Fallback to first method
        }

        // Weight selection based on amount and method type
        if ($amount < 500000) { // < 500K VND - prefer cash
            $cashMethods = $validMethods->where('type', 'cash');
            if ($cashMethods->isNotEmpty()) {
                return $cashMethods->first();
            }
        } elseif ($amount > 5000000) { // > 5M VND - prefer bank transfer
            $bankMethods = $validMethods->where('type', 'bank_transfer');
            if ($bankMethods->isNotEmpty()) {
                return $bankMethods->first();
            }
        }

        return $validMethods->random();
    }

    /**
     * Generate realistic payment date
     */
    private function generatePaymentDate(Carbon $invoiceDate): Carbon
    {
        $daysAfterInvoice = rand(0, 30); // 0-30 days after invoice
        return $invoiceDate->copy()->addDays($daysAfterInvoice);
    }

    /**
     * Generate payment number
     */
    private function generatePaymentNumber(int $storeId): string
    {
        $lastPayment = Payment::where('store_id', $storeId)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastPayment ? ($lastPayment->id + 1) : 1;
        return 'PAY' . str_pad($number, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Determine payment status based on method and amount
     */
    private function determinePaymentStatus(PaymentMethod $method, float $amount): string
    {
        if ($method->type === 'cash') {
            return 'completed'; // Cash payments are immediately completed
        }

        if ($method->requires_verification) {
            return rand(1, 100) <= 95 ? 'completed' : 'pending'; // 95% success rate
        }

        return 'completed';
    }

    /**
     * Generate reference number based on payment method
     */
    private function generateReferenceNumber(PaymentMethod $method): ?string
    {
        return match($method->type) {
            'bank_transfer' => 'REF' . now()->format('YmdHis') . rand(1000, 9999),
            'credit_card', 'debit_card' => 'TXN' . now()->format('YmdHis') . rand(100000, 999999),
            'e_wallet' => strtoupper($method->code) . now()->format('YmdHis') . rand(10000, 99999),
            default => null,
        };
    }

    /**
     * Generate external transaction ID
     */
    private function generateExternalTransactionId(PaymentMethod $method): ?string
    {
        if (in_array($method->type, ['credit_card', 'debit_card', 'e_wallet', 'payment_gateway'])) {
            return strtoupper($method->provider_code) . '_' . now()->format('YmdHis') . '_' . rand(100000, 999999);
        }

        return null;
    }

    /**
     * Generate gateway response data
     */
    private function generateGatewayResponse(PaymentMethod $method): ?array
    {
        if (!in_array($method->type, ['credit_card', 'debit_card', 'e_wallet', 'payment_gateway'])) {
            return null;
        }

        return [
            'response_code' => '00',
            'response_message' => 'Success',
            'transaction_id' => $this->generateExternalTransactionId($method),
            'authorization_code' => 'AUTH' . rand(100000, 999999),
            'gateway_fee' => $method->calculateProcessingFee(1000000), // Sample fee
            'processed_at' => now()->toISOString(),
        ];
    }

    /**
     * Generate payment notes
     */
    private function generatePaymentNotes(PaymentMethod $method, Invoice $invoice): ?string
    {
        $notes = [
            "Thanh toán hóa đơn {$invoice->invoice_number}",
            "Thanh toán qua {$method->name}",
            "Khách hàng: {$invoice->customer_name}",
        ];

        if ($method->type === 'bank_transfer') {
            $notes[] = "Chuyển khoản ngân hàng - Xác nhận tự động";
        } elseif ($method->type === 'cash') {
            $notes[] = "Thanh toán tiền mặt tại quầy";
        }

        return implode('. ', $notes);
    }

    /**
     * Generate payment metadata
     */
    private function generatePaymentMetadata(PaymentMethod $method, Invoice $invoice): array
    {
        $metadata = [
            'invoice_number' => $invoice->invoice_number,
            'customer_name' => $invoice->customer_name,
            'payment_method_code' => $method->code,
            'created_via' => 'seeder',
        ];

        if ($method->type === 'cash') {
            $metadata['cash_register'] = 'POS_001';
            $metadata['cashier'] = 'admin@boxpos.com';
        } elseif ($method->type === 'bank_transfer') {
            $metadata['bank_name'] = 'Vietcombank';
            $metadata['account_number'] = '0123456789';
        }

        return $metadata;
    }

    /**
     * Determine invoice payment status
     */
    private function determineInvoicePaymentStatus(float $paidAmount, float $totalAmount): string
    {
        if ($paidAmount >= $totalAmount) {
            return 'paid';
        } elseif ($paidAmount > 0) {
            return 'partial';
        } else {
            return 'pending';
        }
    }
}