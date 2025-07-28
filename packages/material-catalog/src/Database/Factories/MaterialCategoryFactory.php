<?php

namespace Packages\MaterialCatalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\MaterialCatalog\Models\MaterialCategory;
use Packages\Store\Models\Store;

class MaterialCategoryFactory extends Factory
{
    protected $model = MaterialCategory::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Xi măng Portland', 'Xi măng hỗn hợp', 'Thép cây', 'Tôn thép',
            'Gạch đỏ', 'Gạch bê tông', 'Cát xây dựng', 'Đá dăm',
            'Ngói đất nung', 'Sơn nội thất', 'Sơn ngoại thất'
        ]);

        return [
            'store_id' => Store::factory(),
            'parent_id' => null,
            'code' => strtolower(str_replace(' ', '_', $name)),
            'name' => $name,
            'slug' => \Str::slug($name),
            'description' => "Danh mục {$name} cho vật liệu xây dựng",
            'icon' => $this->faker->randomElement([
                'fas fa-cube', 'fas fa-industry', 'fas fa-th-large',
                'fas fa-mountain', 'fas fa-home', 'fas fa-paint-brush'
            ]),
            'color' => $this->faker->hexColor(),
            'sort_order' => $this->faker->numberBetween(1, 100),
            'is_active' => true,
            'show_in_menu' => true,
            'metadata' => [],
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withParent(MaterialCategory $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'store_id' => $parent->store_id,
        ]);
    }
}