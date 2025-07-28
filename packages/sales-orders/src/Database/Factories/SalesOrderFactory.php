<?php

namespace Packages\SalesOrders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\SalesOrders\Models\SalesOrder;
use Packages\Customer\Models\Customer;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\SalesOrders\Models\SalesOrder>
 */
class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 10000);
        $taxAmount = $subtotal * 0.1; // 10% tax
        $discountAmount = $this->faker->boolean(30) ? $subtotal * 0.05 : 0; // 5% discount sometimes
        $shippingCost = $this->faker->randomFloat(2, 0, 200);
        $totalAmount = $subtotal + $taxAmount + $shippingCost - $discountAmount;
        $paidAmount = $this->faker->boolean(70) ? $totalAmount : $this->faker->randomFloat(2, 0, $totalAmount);
        
        return [
            'store_id' => Store::factory(),
            'customer_id' => Customer::factory(),
            'order_number' => $this->faker->unique()->regexify('SO[0-9]{8}'),
            'customer_reference' => $this->faker->optional()->regexify('[A-Z]{2}[0-9]{4}'),
            'order_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'delivery_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'actual_delivery_date' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'priority' => $this->faker->randomElement(['low', 'normal', 'high', 'urgent']),
            'status' => $this->faker->randomElement(['draft', 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed', 'cancelled']),
            'order_type' => $this->faker->randomElement(['standard', 'rush', 'custom', 'recurring']),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'shipping_cost' => $shippingCost,
            'other_charges' => $this->faker->randomFloat(2, 0, 100),
            'total_amount' => $totalAmount,
            'currency' => 'VND',
            'payment_status' => $this->faker->randomElement(['pending', 'partial', 'paid', 'overdue']),
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'credit_card', 'check']),
            'paid_amount' => $paidAmount,
            'balance_due' => $totalAmount - $paidAmount,
            'payment_due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'delivery_address' => $this->getVietnameseAddress(),
            'delivery_contact' => $this->faker->name(),
            'delivery_phone' => $this->generateVietnamesePhoneNumber(),
            'delivery_instructions' => $this->faker->optional()->sentence(),
            'delivery_method' => $this->faker->randomElement(['pickup', 'delivery', 'shipping']),
            'tracking_number' => $this->faker->optional()->regexify('[A-Z]{2}[0-9]{10}'),
            'sales_channel' => $this->faker->randomElement(['store', 'online', 'phone', 'email']),
            'sales_person_id' => User::factory(),
            'created_by' => User::factory(),
            'approved_by' => $this->faker->boolean(80) ? User::factory() : null,
            'approved_at' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'notes' => $this->faker->optional()->paragraph(),
            'internal_notes' => $this->faker->optional()->sentence(),
            'metadata' => [],
            'is_recurring' => $this->faker->boolean(10),
            'recurring_frequency' => $this->faker->boolean(10) ? $this->faker->randomElement(['weekly', 'monthly', 'quarterly']) : null,
        ];
    }

    /**
     * Create order for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create order for specific customer
     */
    public function forCustomer($customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => is_object($customer) ? $customer->id : $customer,
        ]);
    }

    /**
     * Create completed order
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'payment_status' => 'paid',
            'balance_due' => 0,
            'paid_amount' => $attributes['total_amount'],
            'actual_delivery_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Create pending order
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Create fully paid order
     */
    public function fullyPaid(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_status' => 'paid',
                'paid_amount' => $attributes['total_amount'],
                'balance_due' => 0,
            ];
        });
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
            '987 Cách Mạng Tháng 8, Quận 10, TP.HCM',
            '147 Nguyễn Thị Minh Khai, Quận 1, TP.HCM',
            '258 Lý Tự Trọng, Quận 1, TP.HCM'
        ];
        
        return $this->faker->randomElement($addresses);
    }

    /**
     * Generate realistic Vietnamese phone numbers
     */
    private function generateVietnamesePhoneNumber(): string
    {
        $prefixes = ['090', '091', '094', '083', '084', '085', '081', '082', '032', '033', '034', '035', '036', '037', '038', '039'];
        $prefix = $this->faker->randomElement($prefixes);
        $suffix = $this->faker->numerify('#######');
        
        return $prefix . $suffix;
    }
}