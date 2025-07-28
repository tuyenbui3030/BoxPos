<?php

namespace Packages\MaterialInventory\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\MaterialInventory\Models\MaterialInventory;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\MaterialInventory\Models\MaterialInventory>
 */
class MaterialInventoryFactory extends Factory
{
    protected $model = MaterialInventory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantityOnHand = $this->faker->numberBetween(0, 1000);
        $quantityReserved = $this->faker->numberBetween(0, min(50, $quantityOnHand));
        $quantityAvailable = $quantityOnHand - $quantityReserved;
        $minStock = $this->faker->numberBetween(10, 50);
        $maxStock = $this->faker->numberBetween(500, 2000);
        $unitCost = $this->faker->randomFloat(2, 10000, 500000);
        $totalCost = $quantityOnHand * $unitCost;
        
        return [
            'store_id' => Store::factory(),
            'material_id' => \Packages\MaterialCatalog\Database\Factories\BuildingMaterialFactory::new(),
            'location_code' => $this->faker->regexify('LOC[0-9]{3}'),
            'location_name' => $this->getVietnameseLocationName(),
            'zone' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'aisle' => $this->faker->optional()->numberBetween(1, 20),
            'shelf' => $this->faker->optional()->numberBetween(1, 10),
            'bin' => $this->faker->optional()->numberBetween(1, 50),
            'quantity_on_hand' => $quantityOnHand,
            'quantity_reserved' => $quantityReserved,
            'quantity_available' => $quantityAvailable,
            'quantity_incoming' => $this->faker->numberBetween(0, 100),
            'quantity_outgoing' => $this->faker->numberBetween(0, 50),
            'batch_number' => $this->faker->optional()->regexify('BATCH[0-9]{6}'),
            'serial_numbers' => $this->faker->optional()->randomElements(['SN001', 'SN002', 'SN003', 'SN004', 'SN005'], 2),
            'manufacture_date' => $this->faker->optional()->dateTimeBetween('-2 years', '-6 months'),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('+6 months', '+3 years'),
            'received_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'cost_method' => $this->faker->randomElement(['fifo', 'lifo', 'average', 'specific']),
            'condition' => $this->faker->randomElement(['new', 'good', 'fair', 'damaged']),
            'quality_checked' => $this->faker->boolean(80),
            'last_quality_check' => $this->faker->optional()->dateTimeBetween('-6 months', 'now'),
            'quality_notes' => $this->faker->optional()->sentence(),
            'min_stock_level' => $minStock,
            'max_stock_level' => $maxStock,
            'last_movement_at' => $this->faker->optional()->dateTimeBetween('-3 months', 'now'),
            'movement_count' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Create inventory for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create inventory for specific material
     */
    public function forMaterial($material): static
    {
        return $this->state(fn (array $attributes) => [
            'material_id' => is_object($material) ? $material->id : $material,
        ]);
    }

    /**
     * Create inventory with low stock
     */
    public function lowStock(): static
    {
        $lowQuantity = $this->faker->numberBetween(0, 20);
        return $this->state(fn (array $attributes) => [
            'quantity_on_hand' => $lowQuantity,
            'quantity_available' => $lowQuantity,
            'quantity_reserved' => 0,
        ]);
    }

    /**
     * Create inventory with high stock
     */
    public function highStock(): static
    {
        $highQuantity = $this->faker->numberBetween(500, 2000);
        return $this->state(fn (array $attributes) => [
            'quantity_on_hand' => $highQuantity,
            'quantity_available' => $highQuantity,
            'quantity_reserved' => 0,
        ]);
    }

    /**
     * Create inventory that needs reorder
     */
    public function needsReorder(): static
    {
        $minStock = $this->faker->numberBetween(20, 50);
        $lowQuantity = $this->faker->numberBetween(0, $minStock - 1);
        
        return $this->state(fn (array $attributes) => [
            'quantity_on_hand' => $lowQuantity,
            'quantity_available' => $lowQuantity,
            'quantity_reserved' => 0,
            'min_stock_level' => $minStock,
        ]);
    }

    /**
     * Get Vietnamese warehouse location names
     */
    private function getVietnameseLocationName(): string
    {
        $locations = [
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
            'Khu vực C'
        ];
        
        return $this->faker->randomElement($locations);
    }
}