<?php

namespace Packages\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Products\Models\ProductVariant;
use Packages\Products\Models\Product;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Products\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $costPrice = $this->faker->randomFloat(2, 10, 500);
        $sellingPrice = $costPrice * $this->faker->randomFloat(2, 1.2, 2.5);
        $stockQuantity = $this->faker->numberBetween(0, 50);
        $reservedQuantity = $this->faker->numberBetween(0, min(5, $stockQuantity));
        
        return [
            'product_id' => \Packages\Products\Database\Factories\ProductFactory::new(),
            'sku' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{6}-[A-Z]{2}'),
            'barcode' => $this->faker->unique()->ean13(),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'attributes' => $this->generateVariantAttributes(),
            'attribute_summary' => $this->faker->words(3, true),
            'cost_price' => $costPrice,
            'selling_price' => $sellingPrice,
            'sale_price' => $this->faker->boolean(30) ? $sellingPrice * 0.9 : null,
            'sale_price_start' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween('-1 month', '+1 month') : null,
            'sale_price_end' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween('+1 month', '+3 months') : null,
            'stock_quantity' => $stockQuantity,
            'reserved_quantity' => $reservedQuantity,
            'available_quantity' => $stockQuantity - $reservedQuantity,
            'min_stock_level' => $this->faker->numberBetween(2, 10),
            'reorder_point' => $this->faker->numberBetween(5, 15),
            'weight' => $this->faker->randomFloat(2, 0.1, 5),
            'length' => $this->faker->randomFloat(2, 1, 50),
            'width' => $this->faker->randomFloat(2, 1, 50),
            'height' => $this->faker->randomFloat(2, 1, 50),
            'images' => [],
            'featured_image' => null,
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'is_default' => false,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'metadata' => [],
        ];
    }

    /**
     * Create variant for specific product
     */
    public function forProduct($product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => is_object($product) ? $product->id : $product,
        ]);
    }

    /**
     * Create default variant
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Create active variant
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Create variant with color attribute
     */
    public function withColor(string $color): static
    {
        return $this->state(fn (array $attributes) => [
            'attributes' => array_merge($attributes['attributes'] ?? [], ['color' => $color]),
            'attribute_summary' => $color,
        ]);
    }

    /**
     * Create variant with size attribute
     */
    public function withSize(string $size): static
    {
        return $this->state(fn (array $attributes) => [
            'attributes' => array_merge($attributes['attributes'] ?? [], ['size' => $size]),
            'attribute_summary' => $size,
        ]);
    }

    /**
     * Generate random variant attributes
     */
    private function generateVariantAttributes(): array
    {
        $attributes = [];
        
        // Randomly add color
        if ($this->faker->boolean(60)) {
            $colors = ['Đỏ', 'Xanh', 'Vàng', 'Trắng', 'Đen', 'Xám', 'Nâu', 'Hồng'];
            $attributes['color'] = $this->faker->randomElement($colors);
        }
        
        // Randomly add size
        if ($this->faker->boolean(40)) {
            $sizes = ['S', 'M', 'L', 'XL', 'XXL', 'Nhỏ', 'Vừa', 'Lớn'];
            $attributes['size'] = $this->faker->randomElement($sizes);
        }
        
        // Randomly add material
        if ($this->faker->boolean(30)) {
            $materials = ['Nhựa', 'Kim loại', 'Gỗ', 'Vải', 'Da', 'Thủy tinh'];
            $attributes['material'] = $this->faker->randomElement($materials);
        }
        
        return $attributes;
    }
}