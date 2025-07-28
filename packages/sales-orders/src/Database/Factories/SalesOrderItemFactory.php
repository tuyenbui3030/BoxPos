<?php

namespace Packages\SalesOrders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\SalesOrders\Models\SalesOrderItem;
use Packages\SalesOrders\Models\SalesOrder;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\SalesOrders\Models\SalesOrderItem>
 */
class SalesOrderItemFactory extends Factory
{
    protected $model = SalesOrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 1, 100);
        $unitPrice = $this->faker->randomFloat(2, 10, 1000);
        $discountPercent = $this->faker->boolean(30) ? $this->faker->randomFloat(2, 0, 20) : 0;
        $discountAmount = ($quantity * $unitPrice) * ($discountPercent / 100);
        $taxPercent = $this->faker->randomElement([0, 5, 10]);
        $subtotal = ($quantity * $unitPrice) - $discountAmount;
        $taxAmount = $subtotal * ($taxPercent / 100);
        $lineTotal = $subtotal + $taxAmount;
        $unitCost = $unitPrice * $this->faker->randomFloat(2, 0.6, 0.8); // 60-80% of selling price
        
        return [
            'sales_order_id' => SalesOrder::factory(),
            'item_type' => $this->faker->randomElement(['product', 'service']),
            'item_id' => $this->faker->numberBetween(1, 1000),
            'item_code' => $this->faker->regexify('[A-Z]{3}[0-9]{6}'),
            'item_name' => $this->getVietnameseItemName(),
            'item_description' => $this->faker->sentence(),
            'quantity' => $quantity,
            'delivered_quantity' => $this->faker->randomFloat(2, 0, $quantity),
            'returned_quantity' => 0,
            'unit' => $this->faker->randomElement(['cái', 'bộ', 'chiếc', 'gói', 'hộp', 'kg', 'm3', 'lần']),
            'unit_price' => $unitPrice,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'line_total' => $lineTotal,
            'unit_cost' => $unitCost,
            'total_cost' => $quantity * $unitCost,
            'batch_number' => $this->faker->optional()->regexify('BATCH[0-9]{6}'),
            'serial_numbers' => $this->faker->optional()->regexify('SN[0-9]{8}'),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('+1 month', '+2 years'),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'processing', 'delivered', 'cancelled']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Create item for specific sales order
     */
    public function forSalesOrder($salesOrder): static
    {
        return $this->state(fn (array $attributes) => [
            'sales_order_id' => is_object($salesOrder) ? $salesOrder->id : $salesOrder,
        ]);
    }

    /**
     * Create product item
     */
    public function product(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => 'product',
        ]);
    }

    /**
     * Create service item
     */
    public function service(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => 'service',
            'batch_number' => null,
            'serial_numbers' => null,
            'expiry_date' => null,
        ]);
    }

    /**
     * Create delivered item
     */
    public function delivered(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'delivered',
                'delivered_quantity' => $attributes['quantity'],
            ];
        });
    }

    /**
     * Get Vietnamese item names
     */
    private function getVietnameseItemName(): string
    {
        $items = [
            'Xi măng Portland PCB40',
            'Sắt thép D10',
            'Gạch ống 4 lỗ',
            'Cát vàng xây dựng',
            'Đá dăm 1x2',
            'Ngói lợp Đồng Nai',
            'Ống nước PVC D90',
            'Dây điện Cadivi 2.5',
            'Sơn nước Dulux',
            'Keo dán gạch Davco',
            'Vữa khô Weber',
            'Tôn lạnh 0.5mm',
            'Nhựa đường',
            'Gỗ thông xẻ',
            'Kính cường lực 8mm',
            'Dịch vụ thi công móng',
            'Dịch vụ vận chuyển',
            'Dịch vụ tư vấn thiết kế',
            'Dịch vụ giám sát',
            'Dịch vụ bảo trì'
        ];
        
        return $this->faker->randomElement($items);
    }
}