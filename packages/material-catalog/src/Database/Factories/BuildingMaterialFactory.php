<?php

namespace Packages\MaterialCatalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\MaterialCatalog\Models\MaterialCategory;
use Packages\MaterialCatalog\Models\MaterialUnit;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class BuildingMaterialFactory extends Factory
{
    protected $model = BuildingMaterial::class;

    public function definition(): array
    {
        $materials = [
            'Xi măng Portland PCB40', 'Xi măng hỗn hợp PCB30', 'Thép cây D10', 'Thép cây D12',
            'Tôn lạnh 0.5mm', 'Tôn màu xanh', 'Gạch đỏ 220x110x60', 'Gạch bê tông 390x190x190',
            'Cát xây dựng', 'Đá dăm 1x2', 'Ngói đất nung', 'Sơn Dulux nội thất',
            'Sơn Jotun ngoại thất', 'Ống PVC D90', 'Ống thép D21'
        ];

        $brands = [
            'Hòa Phát', 'Pomina', 'Hà Tiên', 'Holcim', 'Dulux', 'Jotun',
            'Tôn Đông Á', 'Gạch Đồng Tâm', 'Viglacera', 'Bình Minh'
        ];

        $name = $this->faker->randomElement($materials);
        $brand = $this->faker->randomElement($brands);

        return [
            'store_id' => Store::factory(),
            'category_id' => MaterialCategory::factory(),
            'primary_unit_id' => MaterialUnit::factory(),
            'material_code' => 'MAT' . str_pad($this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'name' => $name,
            'slug' => \Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 99999),
            'description' => "Vật liệu {$name} chất lượng cao từ thương hiệu {$brand}",
            'short_description' => "Vật liệu {$name} - {$brand}",
            'brand' => $brand,
            'model' => $this->faker->optional()->bothify('??##'),
            'origin_country' => $this->faker->randomElement(['Việt Nam', 'Trung Quốc', 'Thái Lan', 'Malaysia']),
            'images' => [],
            'barcode' => $this->faker->optional()->ean13(),
            'qr_code' => $this->faker->optional()->uuid(),
            'weight_per_unit' => $this->faker->randomFloat(3, 0.1, 1000),
            'dimensions' => [
                'length' => $this->faker->randomFloat(2, 10, 500),
                'width' => $this->faker->randomFloat(2, 10, 500),
                'height' => $this->faker->randomFloat(2, 1, 100),
                'unit' => 'cm'
            ],
            'is_hazardous' => $this->faker->boolean(10),
            'storage_requirements' => [
                'temperature' => 'Nhiệt độ phòng',
                'humidity' => 'Tránh ẩm ướt',
                'special' => 'Bảo quản nơi khô ráo, thoáng mát'
            ],
            'quality_standards' => ['TCVN', 'ISO'],
            'certifications' => ['CR', 'QUATEST'],
            'expiry_date' => $this->faker->optional()->dateTimeBetween('+1 year', '+5 years'),
            'shelf_life_days' => $this->faker->optional()->numberBetween(365, 1825),
            'requires_quality_check' => $this->faker->boolean(30),
            'is_active' => true,
            'is_featured' => $this->faker->boolean(20),
            'track_serial_numbers' => $this->faker->boolean(10),
            'track_batch_numbers' => $this->faker->boolean(30),
            'technical_specs' => [
                'strength' => $this->faker->randomElement(['Cao', 'Trung bình', 'Thấp']),
                'durability' => $this->faker->randomElement(['Tốt', 'Khá', 'Trung bình']),
                'fire_resistance' => $this->faker->randomElement(['A1', 'A2', 'B', 'C'])
            ],
            'tags' => $this->faker->randomElements(['xây dựng', 'nội thất', 'ngoại thất', 'chống thấm', 'cách nhiệt'], 2),
            'internal_notes' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function hazardous(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_hazardous' => true,
        ]);
    }

    public function withCategory(MaterialCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
            'store_id' => $category->store_id,
        ]);
    }

    public function withUnit(MaterialUnit $unit): static
    {
        return $this->state(fn (array $attributes) => [
            'primary_unit_id' => $unit->id,
            'store_id' => $unit->store_id,
        ]);
    }
}