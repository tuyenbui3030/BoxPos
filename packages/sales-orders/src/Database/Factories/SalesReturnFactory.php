<?php

namespace Packages\SalesOrders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\SalesOrders\Models\SalesReturn;
use Packages\SalesOrders\Models\SalesOrder;
use Packages\SalesOrders\Models\Invoice;
use Packages\Customer\Models\Customer;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\SalesOrders\Models\SalesReturn>
 */
class SalesReturnFactory extends Factory
{
    protected $model = SalesReturn::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 50, 2000);
        $taxAmount = $subtotal * 0.1;
        $totalAmount = $subtotal + $taxAmount;
        $restockingFee = $this->faker->boolean(30) ? $totalAmount * 0.1 : 0;
        $refundAmount = $totalAmount - $restockingFee;
        
        return [
            'store_id' => Store::factory(),
            'sales_order_id' => SalesOrder::factory(),
            'invoice_id' => Invoice::factory(),
            'customer_id' => Customer::factory(),
            'return_number' => $this->faker->unique()->regexify('RET[0-9]{8}'),
            'return_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'return_type' => $this->faker->randomElement(['full', 'partial', 'exchange']),
            'status' => $this->faker->randomElement(['pending', 'approved', 'processing', 'completed', 'rejected']),
            'return_reason' => $this->faker->randomElement(['defective', 'wrong_item', 'damaged', 'not_needed', 'quality_issue', 'other']),
            'return_reason_detail' => $this->getVietnameseReturnReason(),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'currency' => 'VND',
            'refund_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'credit_card', 'store_credit']),
            'refund_status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']),
            'refund_amount' => $refundAmount,
            'restocking_fee' => $restockingFee,
            'refund_processed_date' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'restock_items' => $this->faker->boolean(70),
            'item_condition' => $this->faker->randomElement(['new', 'good', 'fair', 'poor', 'damaged']),
            'processed_by' => User::factory(),
            'approved_by' => $this->faker->boolean(80) ? User::factory() : null,
            'approved_at' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'notes' => $this->faker->optional()->paragraph(),
            'internal_notes' => $this->faker->optional()->sentence(),
            'attachments' => [],
            'metadata' => [],
        ];
    }

    /**
     * Create return for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create return for specific sales order
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
     * Create approved return
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Create completed return
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'refund_status' => 'completed',
            'refund_processed_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Create defective item return
     */
    public function defective(): static
    {
        return $this->state(fn (array $attributes) => [
            'return_reason' => 'defective',
            'return_reason_detail' => 'Sản phẩm bị lỗi kỹ thuật',
            'item_condition' => 'damaged',
            'restock_items' => false,
            'restocking_fee' => 0,
        ]);
    }

    /**
     * Get Vietnamese return reasons
     */
    private function getVietnameseReturnReason(): string
    {
        $reasons = [
            'Sản phẩm bị lỗi kỹ thuật',
            'Giao sai hàng',
            'Hàng bị hư hỏng trong vận chuyển',
            'Không còn nhu cầu sử dụng',
            'Chất lượng không đạt yêu cầu',
            'Kích thước không phù hợp',
            'Màu sắc không đúng như mô tả',
            'Khách hàng đổi ý',
            'Sản phẩm không đúng thông số kỹ thuật',
            'Lý do khác'
        ];
        
        return $this->faker->randomElement($reasons);
    }
}