<?php

namespace Packages\Warehouse\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Warehouse\Models\InventoryMovement;
use Packages\MaterialInventory\Models\MaterialInventory;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Warehouse\Models\InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    protected $model = InventoryMovement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $movementType = $this->faker->randomElement(['in', 'out', 'transfer', 'adjustment']);
        $quantity = $this->faker->numberBetween(1, 100);
        $unitCost = $this->faker->randomFloat(2, 10000, 500000);
        
        return [
            'store_id' => Store::factory(),
            'inventory_id' => \Packages\MaterialInventory\Database\Factories\MaterialInventoryFactory::new(),
            'movement_code' => $this->faker->unique()->regexify('MOV[0-9]{8}'),
            'movement_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'movement_type' => $movementType,
            'quantity' => $movementType === 'out' ? -$quantity : $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => abs($quantity) * $unitCost,
            'balance_before' => $this->faker->numberBetween(0, 1000),
            'balance_after' => function (array $attributes) {
                return $attributes['balance_before'] + $attributes['quantity'];
            },
            'reference_type' => $this->faker->optional()->randomElement(['purchase_order', 'sales_order', 'transfer', 'adjustment', 'stocktake']),
            'reference_id' => $this->faker->optional()->numberBetween(1, 1000),
            'reference_number' => $this->faker->optional()->regexify('REF[0-9]{6}'),
            'reason' => $this->getVietnameseMovementReason($movementType),
            'notes' => $this->faker->optional()->sentence(),
            'location_from' => $this->faker->optional()->randomElement($this->getVietnameseLocations()),
            'location_to' => $this->faker->optional()->randomElement($this->getVietnameseLocations()),
            'batch_number' => $this->faker->optional()->regexify('BATCH[0-9]{6}'),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('+6 months', '+2 years'),
            'supplier_id' => $this->faker->optional()->numberBetween(1, 50),
            'customer_id' => $this->faker->optional()->numberBetween(1, 100),
            'is_confirmed' => true,
            'confirmed_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'confirmed_by' => User::factory(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create movement for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create movement for specific inventory
     */
    public function forInventory($inventory): static
    {
        return $this->state(fn (array $attributes) => [
            'inventory_id' => is_object($inventory) ? $inventory->id : $inventory,
        ]);
    }

    /**
     * Create inbound movement
     */
    public function inbound(): static
    {
        return $this->state(fn (array $attributes) => [
            'movement_type' => 'in',
            'quantity' => $this->faker->numberBetween(10, 500),
            'reason' => $this->getVietnameseInboundReason(),
            'reference_type' => 'purchase_order',
        ]);
    }

    /**
     * Create outbound movement
     */
    public function outbound(): static
    {
        return $this->state(fn (array $attributes) => [
            'movement_type' => 'out',
            'quantity' => -$this->faker->numberBetween(1, 100),
            'reason' => $this->getVietnameseOutboundReason(),
            'reference_type' => 'sales_order',
        ]);
    }

    /**
     * Create transfer movement
     */
    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'movement_type' => 'transfer',
            'quantity' => $this->faker->numberBetween(1, 50),
            'reason' => 'Chuyển kho nội bộ',
            'reference_type' => 'transfer',
            'location_from' => $this->faker->randomElement($this->getVietnameseLocations()),
            'location_to' => $this->faker->randomElement($this->getVietnameseLocations()),
        ]);
    }

    /**
     * Create adjustment movement
     */
    public function adjustment(): static
    {
        return $this->state(fn (array $attributes) => [
            'movement_type' => 'adjustment',
            'quantity' => $this->faker->randomElement([-10, -5, -2, 2, 5, 10]),
            'reason' => $this->getVietnameseAdjustmentReason(),
            'reference_type' => 'adjustment',
        ]);
    }

    /**
     * Create confirmed movement
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_confirmed' => true,
            'confirmed_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'confirmed_by' => User::factory(),
        ]);
    }

    /**
     * Create unconfirmed movement
     */
    public function unconfirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_confirmed' => false,
            'confirmed_at' => null,
            'confirmed_by' => null,
        ]);
    }

    /**
     * Get Vietnamese movement reasons based on type
     */
    private function getVietnameseMovementReason(string $type): string
    {
        switch ($type) {
            case 'in':
                return $this->getVietnameseInboundReason();
            case 'out':
                return $this->getVietnameseOutboundReason();
            case 'transfer':
                return 'Chuyển kho nội bộ';
            case 'adjustment':
                return $this->getVietnameseAdjustmentReason();
            default:
                return 'Xuất nhập kho';
        }
    }

    /**
     * Get Vietnamese inbound reasons
     */
    private function getVietnameseInboundReason(): string
    {
        $reasons = [
            'Nhập hàng từ nhà cung cấp',
            'Nhập hàng mới',
            'Nhập bổ sung tồn kho',
            'Nhập hàng đặt trước',
            'Nhập hàng khuyến mãi',
            'Nhập hàng trả lại',
            'Nhập hàng chuyển kho',
            'Nhập hàng sản xuất',
            'Nhập hàng tặng kèm',
            'Nhập hàng bảo hành'
        ];
        
        return $this->faker->randomElement($reasons);
    }

    /**
     * Get Vietnamese outbound reasons
     */
    private function getVietnameseOutboundReason(): string
    {
        $reasons = [
            'Xuất bán cho khách hàng',
            'Xuất hàng theo đơn',
            'Xuất hàng giao dịch',
            'Xuất hàng chuyển kho',
            'Xuất hàng trả nhà cung cấp',
            'Xuất hàng hỏng',
            'Xuất hàng khuyến mãi',
            'Xuất hàng mẫu',
            'Xuất hàng sử dụng nội bộ',
            'Xuất hàng thanh lý'
        ];
        
        return $this->faker->randomElement($reasons);
    }

    /**
     * Get Vietnamese adjustment reasons
     */
    private function getVietnameseAdjustmentReason(): string
    {
        $reasons = [
            'Điều chỉnh sau kiểm kê',
            'Điều chỉnh do lỗi nhập liệu',
            'Điều chỉnh hàng hỏng',
            'Điều chỉnh hàng mất',
            'Điều chỉnh hàng thừa',
            'Điều chỉnh do hết hạn',
            'Điều chỉnh do sai sót',
            'Điều chỉnh theo chỉ đạo',
            'Điều chỉnh hàng lỗi',
            'Điều chỉnh khác'
        ];
        
        return $this->faker->randomElement($reasons);
    }

    /**
     * Get Vietnamese warehouse locations
     */
    private function getVietnameseLocations(): array
    {
        return [
            'Kho A - Kệ 1',
            'Kho A - Kệ 2',
            'Kho A - Kệ 3',
            'Kho B - Kệ 1',
            'Kho B - Kệ 2',
            'Kho C - Sân',
            'Kho C - Kệ 1',
            'Kho D - Tầng 1',
            'Kho D - Tầng 2',
            'Sân phơi',
            'Khu vực A1',
            'Khu vực A2',
            'Khu vực B1',
            'Khu vực B2',
            'Khu vực C',
            'Kho lạnh',
            'Kho khô',
            'Kho nguyên liệu',
            'Kho thành phẩm',
            'Kho tạm'
        ];
    }
}