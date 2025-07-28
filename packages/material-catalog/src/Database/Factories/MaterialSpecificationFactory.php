<?php

namespace Packages\MaterialCatalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\MaterialCatalog\Models\MaterialSpecification;
use Packages\MaterialCatalog\Models\BuildingMaterial;

class MaterialSpecificationFactory extends Factory
{
    protected $model = MaterialSpecification::class;

    public function definition(): array
    {
        $specifications = [
            ['name' => 'Độ bền nén', 'unit' => 'MPa', 'type' => 'number', 'category' => 'Cơ học'],
            ['name' => 'Độ bền kéo', 'unit' => 'MPa', 'type' => 'number', 'category' => 'Cơ học'],
            ['name' => 'Khối lượng riêng', 'unit' => 'kg/m³', 'type' => 'number', 'category' => 'Vật lý'],
            ['name' => 'Độ hút nước', 'unit' => '%', 'type' => 'number', 'category' => 'Vật lý'],
            ['name' => 'Màu sắc', 'unit' => '', 'type' => 'text', 'category' => 'Ngoại quan'],
            ['name' => 'Kích thước', 'unit' => 'mm', 'type' => 'text', 'category' => 'Kích thước'],
            ['name' => 'Độ dày', 'unit' => 'mm', 'type' => 'number', 'category' => 'Kích thước'],
            ['name' => 'Chống cháy', 'unit' => '', 'type' => 'text', 'category' => 'An toàn'],
        ];

        $spec = $this->faker->randomElement($specifications);

        return [
            'material_id' => \Packages\MaterialCatalog\Database\Factories\BuildingMaterialFactory::new(),
            'spec_name' => $spec['name'],
            'spec_value' => $spec['type'] === 'number' 
                ? $this->faker->randomFloat(2, 1, 1000) 
                : $this->faker->randomElement(['Xanh', 'Đỏ', 'Trắng', 'Xám', 'Vàng']),
            'spec_unit' => $spec['unit'],
            'spec_type' => $spec['type'],
            'spec_category' => $spec['category'],
            'description' => "Thông số {$spec['name']} của vật liệu",
            'sort_order' => $this->faker->numberBetween(1, 100),
            'is_required' => $this->faker->boolean(30),
            'is_searchable' => $this->faker->boolean(50),
            'show_in_listing' => $this->faker->boolean(70),
        ];
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }

    public function searchable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_searchable' => true,
        ]);
    }

    public function inListing(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_in_listing' => true,
        ]);
    }

    public function forMaterial(BuildingMaterial $material): static
    {
        return $this->state(fn (array $attributes) => [
            'material_id' => $material->id,
        ]);
    }
}