<?php

namespace Packages\SalesOrders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\SalesOrders\Models\InvoiceItem;
use Packages\SalesOrders\Models\Invoice;
use Packages\SalesOrders\Models\SalesOrderItem;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\SalesOrders\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 1, 100);
        $unitPrice = $this->faker->randomFloat(2, 10, 1000);
        $discountPercent = $this->faker->boolean(20) ? $this->faker->randomFloat(2, 0, 15) : 0;
        $discountAmount = ($quantity * $unitPrice) * ($discountPercent / 100);
        $taxPercent = $this->faker->randomElement([0, 5, 10]);
        $subtotal = ($quantity * $unitPrice) - $discountAmount;
        $taxAmount = $subtotal * ($taxPercent / 100);
        $lineTotal = $subtotal + $taxAmount;
        $unitCost = $unitPrice * $this->faker->randomFloat(2, 0.6, 0.8);
        
        return [
            'invoice_id' => Invoice::factory(),
            'sales_order_item_id' => SalesOrderItem::factory(),
            'item_type' => $this->faker->randomElement(['product', 'service']),
            'item_id' => $this->faker->numberBetween(1, 1000),
            'item_code' => $this->faker->regexify('[A-Z]{3}[0-9]{6}'),
            'item_name' => $this->getVietnameseItemName(),
            'item_description' => $this->faker->sentence(),
            'quantity' => $quantity,
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
            'notes' => $this->faker->optional()->sentence(),
            'metadata' => [],
        ];
    }

    /**
     * Create item for specific invoice
     */
    public function forInvoice($invoice): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_id' => is_object($invoice) ? $invoice->id : $invoice,
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