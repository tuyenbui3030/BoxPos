<?php

namespace Packages\SalesOrders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\SalesOrders\Models\SalesReturnItem;
use Packages\SalesOrders\Models\SalesReturn;
use Packages\SalesOrders\Models\SalesOrderItem;
use Packages\SalesOrders\Models\InvoiceItem;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\SalesOrders\Models\SalesReturnItem>
 */
class SalesReturnItemFactory extends Factory
{
    protected $model = SalesReturnItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $originalQuantity = $this->faker->randomFloat(2, 1, 100);
        $returnQuantity = $this->faker->randomFloat(2, 1, $originalQuantity);
        $originalUnitPrice = $this->faker->randomFloat(2, 10, 1000);
        $returnUnitPrice = $originalUnitPrice; // Usually same price for returns
        $lineTotal = $returnQuantity * $returnUnitPrice;
        
        return [
            'sales_return_id' => SalesReturn::factory(),
            'sales_order_item_id' => SalesOrderItem::factory(),
            'invoice_item_id' => InvoiceItem::factory(),
            'item_type' => $this->faker->randomElement(['product', 'service']),
            'item_id' => $this->faker->numberBetween(1, 1000),
            'item_code' => $this->faker->regexify('[A-Z]{3}[0-9]{6}'),
            'item_name' => $this->getVietnameseItemName(),
            'item_description' => $this->faker->sentence(),
            'original_quantity' => $originalQuantity,
            'return_quantity' => $returnQuantity,
            'unit' => $this->faker->randomElement(['cái', 'bộ', 'chiếc', 'gói', 'hộp', 'kg', 'm3']),
            'original_unit_price' => $originalUnitPrice,
            'return_unit_price' => $returnUnitPrice,
            'line_total' => $lineTotal,
            'return_reason' => $this->faker->randomElement(['defective', 'wrong_item', 'damaged', 'not_needed', 'quality_issue']),
            'return_reason_detail' => $this->getVietnameseReturnReason(),
            'item_condition' => $this->faker->randomElement(['new', 'good', 'fair', 'poor', 'damaged']),
            'can_restock' => $this->faker->boolean(60),
            'batch_number' => $this->faker->optional()->regexify('BATCH[0-9]{6}'),
            'serial_numbers' => $this->faker->optional()->regexify('SN[0-9]{8}'),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('+1 month', '+2 years'),
            'notes' => $this->faker->optional()->sentence(),
            'metadata' => [],
        ];
    }

    /**
     * Create item for specific sales return
     */
    public function forSalesReturn($salesReturn): static
    {
        return $this->state(fn (array $attributes) => [
            'sales_return_id' => is_object($salesReturn) ? $salesReturn->id : $salesReturn,
        ]);
    }

    /**
     * Create full return item
     */
    public function fullReturn(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'return_quantity' => $attributes['original_quantity'],
            ];
        });
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
            'can_restock' => false,
        ]);
    }

    /**
     * Create restockable item
     */
    public function restockable(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_condition' => $this->faker->randomElement(['new', 'good']),
            'can_restock' => true,
        ]);
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
            'Keo dán gạch Davco'
        ];
        
        return $this->faker->randomElement($items);
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
            'Sản phẩm không đúng thông số kỹ thuật'
        ];
        
        return $this->faker->randomElement($reasons);
    }
}