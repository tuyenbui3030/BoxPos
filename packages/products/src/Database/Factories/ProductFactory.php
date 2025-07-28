<?php

namespace Packages\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Products\Models\Product;
use Packages\Products\Models\ProductCategory;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Products\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $costPrice = $this->faker->randomFloat(2, 10, 500);
        $sellingPrice = $costPrice * $this->faker->randomFloat(2, 1.2, 2.5); // 20-150% markup
        $stockQuantity = $this->faker->numberBetween(0, 100);
        $reservedQuantity = $this->faker->numberBetween(0, min(10, $stockQuantity));
        
        return [
            'store_id' => Store::factory(),
            'category_id' => \Packages\Products\Database\Factories\ProductCategoryFactory::new(),
            'sku' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{6}'),
            'barcode' => $this->faker->unique()->ean13(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'short_description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['simple', 'variable', 'grouped']),
            'status' => $this->faker->randomElement(['active', 'inactive', 'draft']),
            'is_digital' => false,
            'is_service' => false,
            'cost_price' => $costPrice,
            'selling_price' => $sellingPrice,
            'sale_price' => $this->faker->boolean(30) ? $sellingPrice * 0.9 : null,
            'sale_price_start' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween('-1 month', '+1 month') : null,
            'sale_price_end' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween('+1 month', '+3 months') : null,
            'currency' => 'VND',
            'is_taxable' => $this->faker->boolean(80),
            'tax_rate' => $this->faker->randomElement([0, 5, 10]),
            'tax_class' => 'standard',
            'track_inventory' => true,
            'stock_quantity' => $stockQuantity,
            'reserved_quantity' => $reservedQuantity,
            'available_quantity' => $stockQuantity - $reservedQuantity,
            'unit' => $this->faker->randomElement(['cái', 'bộ', 'chiếc', 'gói', 'hộp']),
            'min_stock_level' => $this->faker->numberBetween(5, 20),
            'max_stock_level' => $this->faker->numberBetween(100, 500),
            'reorder_point' => $this->faker->numberBetween(10, 30),
            'reorder_quantity' => $this->faker->numberBetween(50, 200),
            'weight' => $this->faker->randomFloat(2, 0.1, 10),
            'weight_unit' => 'kg',
            'length' => $this->faker->randomFloat(2, 1, 100),
            'width' => $this->faker->randomFloat(2, 1, 100),
            'height' => $this->faker->randomFloat(2, 1, 100),
            'dimension_unit' => 'cm',
            'images' => [],
            'featured_image' => null,
            'meta_title' => null,
            'meta_description' => null,
            'tags' => [],
            'supplier_sku' => $this->faker->optional()->regexify('[A-Z]{2}[0-9]{4}'),
            'manufacturer' => $this->faker->optional()->company(),
            'brand' => $this->faker->optional()->word(),
            'model' => $this->faker->optional()->bothify('Model-##??'),
            'allow_backorder' => $this->faker->boolean(20),
            'is_featured' => $this->faker->boolean(10),
            'requires_shipping' => true,
            'sort_order' => $this->faker->numberBetween(1, 1000),
            'attributes' => [],
            'metadata' => [],
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create product for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create product for specific category
     */
    public function forCategory($category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => is_object($category) ? $category->id : $category,
        ]);
    }

    /**
     * Create active product
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Create featured product
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Create product on sale
     */
    public function onSale(): static
    {
        return $this->state(function (array $attributes) {
            $sellingPrice = $attributes['selling_price'] ?? 100;
            return [
                'sale_price' => $sellingPrice * 0.8, // 20% discount
                'sale_price_start' => now()->subDays(7),
                'sale_price_end' => now()->addDays(30),
            ];
        });
    }

    /**
     * Create out of stock product
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
            'reserved_quantity' => 0,
            'available_quantity' => 0,
        ]);
    }

    /**
     * Create low stock product
     */
    public function lowStock(): static
    {
        return $this->state(function (array $attributes) {
            $minLevel = $attributes['min_stock_level'] ?? 10;
            $lowStock = $this->faker->numberBetween(1, $minLevel);
            
            return [
                'stock_quantity' => $lowStock,
                'reserved_quantity' => 0,
                'available_quantity' => $lowStock,
            ];
        });
    }

    /**
     * Create digital product
     */
    public function digital(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_digital' => true,
            'track_inventory' => false,
            'requires_shipping' => false,
            'weight' => 0,
            'length' => 0,
            'width' => 0,
            'height' => 0,
        ]);
    }

    /**
     * Create service product
     */
    public function service(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_service' => true,
            'track_inventory' => false,
            'requires_shipping' => false,
            'weight' => 0,
            'length' => 0,
            'width' => 0,
            'height' => 0,
        ]);
    }
}