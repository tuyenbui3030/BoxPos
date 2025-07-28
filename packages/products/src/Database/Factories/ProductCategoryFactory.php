<?php

namespace Packages\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Products\Models\ProductCategory;
use Packages\Store\Models\Store;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Products\Models\ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'parent_id' => null,
            'code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'image' => null,
            'color' => $this->faker->hexColor(),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'metadata' => [],
        ];
    }

    /**
     * Create category for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create subcategory with parent
     */
    public function withParent($parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => is_object($parent) ? $parent->id : $parent,
            'store_id' => is_object($parent) ? $parent->store_id : Store::find($parent)->store_id,
        ]);
    }

    /**
     * Create inactive category
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create category with Vietnamese service names
     */
    public function vietnameseService(): static
    {
        $serviceCategories = [
            'Dịch vụ thi công',
            'Dịch vụ vận chuyển', 
            'Dịch vụ tư vấn',
            'Dịch vụ bảo trì',
            'Dịch vụ thiết kế',
            'Dịch vụ lắp đặt',
            'Dịch vụ sửa chữa',
            'Dịch vụ khác'
        ];

        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement($serviceCategories),
            'description' => 'Danh mục dịch vụ chuyên nghiệp',
        ]);
    }
}