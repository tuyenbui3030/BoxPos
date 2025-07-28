<?php

namespace Packages\SalesOrders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\SalesOrders\Models\Invoice;
use Packages\SalesOrders\Models\SalesOrder;
use Packages\Customer\Models\Customer;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\SalesOrders\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 10000);
        $taxAmount = $subtotal * 0.1; // 10% tax
        $discountAmount = $this->faker->boolean(20) ? $subtotal * 0.05 : 0;
        $shippingCost = $this->faker->randomFloat(2, 0, 200);
        $totalAmount = $subtotal + $taxAmount + $shippingCost - $discountAmount;
        $paidAmount = $this->faker->boolean(60) ? $totalAmount : $this->faker->randomFloat(2, 0, $totalAmount);
        
        return [
            'store_id' => Store::factory(),
            'sales_order_id' => SalesOrder::factory(),
            'customer_id' => Customer::factory(),
            'invoice_number' => $this->faker->unique()->regexify('INV[0-9]{8}'),
            'tax_invoice_number' => $this->faker->optional()->regexify('TAX[0-9]{8}'),
            'invoice_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'invoice_type' => $this->faker->randomElement(['standard', 'proforma', 'credit_note', 'debit_note']),
            'status' => $this->faker->randomElement(['draft', 'sent', 'viewed', 'paid', 'overdue', 'cancelled']),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'shipping_cost' => $shippingCost,
            'other_charges' => $this->faker->randomFloat(2, 0, 100),
            'total_amount' => $totalAmount,
            'currency' => 'VND',
            'payment_status' => $this->faker->randomElement(['pending', 'partial', 'paid', 'overdue']),
            'paid_amount' => $paidAmount,
            'balance_due' => $totalAmount - $paidAmount,
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'credit_card', 'check']),
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->email(),
            'customer_phone' => $this->generateVietnamesePhoneNumber(),
            'billing_address' => $this->getVietnameseAddress(),
            'shipping_address' => $this->getVietnameseAddress(),
            'customer_tax_code' => $this->faker->optional()->regexify('[0-9]{10}'),
            'tax_rate' => 10,
            'is_tax_inclusive' => $this->faker->boolean(30),
            'is_e_invoice' => $this->faker->boolean(20),
            'e_invoice_code' => $this->faker->optional()->regexify('E[0-9]{10}'),
            'e_invoice_sent_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'e_invoice_data' => [],
            'sales_person_id' => User::factory(),
            'created_by' => User::factory(),
            'approved_by' => $this->faker->boolean(80) ? User::factory() : null,
            'approved_at' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'notes' => $this->faker->optional()->paragraph(),
            'terms_conditions' => 'Thanh toán trong vòng 30 ngày kể từ ngày xuất hóa đơn.',
            'metadata' => [],
        ];
    }

    /**
     * Create invoice for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create invoice for specific sales order
     */
    public function forSalesOrder($salesOrder): static
    {
        return $this->state(fn (array $attributes) => [
            'sales_order_id' => is_object($salesOrder) ? $salesOrder->id : $salesOrder,
            'customer_id' => is_object($salesOrder) ? $salesOrder->customer_id : SalesOrder::find($salesOrder)->customer_id,
            'store_id' => is_object($salesOrder) ? $salesOrder->store_id : SalesOrder::find($salesOrder)->store_id,
        ]);
    }

    /**
     * Create paid invoice
     */
    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'paid',
                'payment_status' => 'paid',
                'paid_amount' => $attributes['total_amount'],
                'balance_due' => 0,
            ];
        });
    }

    /**
     * Create overdue invoice
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'payment_status' => 'overdue',
            'due_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    /**
     * Create e-invoice
     */
    public function eInvoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_e_invoice' => true,
            'e_invoice_code' => $this->faker->regexify('E[0-9]{10}'),
            'e_invoice_sent_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Get Vietnamese addresses
     */
    private function getVietnameseAddress(): string
    {
        $addresses = [
            '123 Nguyễn Huệ, Quận 1, TP.HCM',
            '456 Lê Lợi, Quận 3, TP.HCM',
            '789 Trần Hưng Đạo, Quận 5, TP.HCM',
            '321 Võ Văn Tần, Quận 3, TP.HCM',
            '654 Điện Biên Phủ, Quận Bình Thạnh, TP.HCM',
            '987 Cách Mạng Tháng 8, Quận 10, TP.HCM'
        ];
        
        return $this->faker->randomElement($addresses);
    }

    /**
     * Generate realistic Vietnamese phone numbers
     */
    private function generateVietnamesePhoneNumber(): string
    {
        $prefixes = ['090', '091', '094', '083', '084', '085', '081', '082', '032', '033', '034', '035'];
        $prefix = $this->faker->randomElement($prefixes);
        $suffix = $this->faker->numerify('#######');
        
        return $prefix . $suffix;
    }
}