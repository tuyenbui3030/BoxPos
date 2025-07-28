<?php

namespace Packages\MaterialCatalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\MaterialCatalog\Models\MaterialUnit;
use Packages\Store\Models\Store;

class MaterialUnitFactory extends Factory
{
    protected $model = MaterialUnit::class;

    public function definition(): array
    {
        $units = [
            ['code' => 'kg', 'name' => 'Kilogram', 'symbol' => 'kg', 'type' => 'weight'],
            ['code' => 'ton', 'name' => 'Tấn', 'symbol' => 'tấn', 'type' => 'weight'],
            ['code' => 'm3', 'name' => 'Mét khối', 'symbol' => 'm³', 'type' => 'volume'],
            ['code' => 'm2', 'name' => 'Mét vuông', 'symbol' => 'm²', 'type' => 'area'],
            ['code' => 'piece', 'name' => 'Cái', 'symbol' => 'cái', 'type' => 'count'],
            ['code' => 'bag', 'name' => 'Bao', 'symbol' => 'bao', 'type' => 'count'],
        ];

        $unit = $this->faker->randomElement($units);

        return [
            'store_id' => Store::factory(),
            'code' => $unit['code'],
            'name' => $unit['name'],
            'symbol' => $unit['symbol'],
            'type' => $unit['type'],
            'conversion_factor' => $this->faker->randomFloat(4, 0.001, 1000),
            'base_unit_code' => $unit['code'],
            'description' => "Đơn vị {$unit['name']} cho vật liệu xây dựng",
            'is_active' => true,
            'is_default' => false,
        ];
    }

    public function weight(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'weight',
            'base_unit_code' => 'kg',
        ]);
    }

    public function volume(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'volume',
            'base_unit_code' => 'm3',
        ]);
    }

    public function countType(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'count',
            'base_unit_code' => 'piece',
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}