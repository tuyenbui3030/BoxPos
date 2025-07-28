<?php

namespace Packages\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Products\Models\Service;
use Packages\Products\Models\ProductCategory;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Products\Models\Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $basePrice = $this->faker->randomFloat(2, 50, 2000);
        $hourlyRate = $this->faker->randomFloat(2, 20, 500);
        
        return [
            'store_id' => Store::factory(),
            'category_id' => \Packages\Products\Database\Factories\ProductCategoryFactory::new(),
            'code' => $this->faker->unique()->regexify('SRV[0-9]{6}'),
            'name' => $this->getVietnameseServiceName(),
            'description' => $this->faker->paragraph(),
            'short_description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['standard', 'custom', 'subscription', 'one_time']),
            'status' => $this->faker->randomElement(['active', 'inactive', 'draft']),
            'billing_type' => $this->faker->randomElement(['fixed', 'hourly', 'daily', 'monthly']),
            'base_price' => $basePrice,
            'hourly_rate' => $hourlyRate,
            'setup_fee' => $this->faker->boolean(30) ? $this->faker->randomFloat(2, 10, 200) : 0,
            'currency' => 'VND',
            'estimated_duration' => $this->faker->numberBetween(30, 480), // 30 minutes to 8 hours
            'min_duration' => $this->faker->numberBetween(15, 60),
            'max_duration' => $this->faker->numberBetween(240, 960),
            'requires_booking' => $this->faker->boolean(70),
            'booking_lead_time' => $this->faker->numberBetween(1, 48), // hours
            'available_days' => $this->faker->randomElements(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], $this->faker->numberBetween(3, 7)),
            'available_from' => $this->faker->time('H:i', '09:00'),
            'available_to' => $this->faker->time('H:i', '18:00'),
            'is_taxable' => $this->faker->boolean(80),
            'tax_rate' => $this->faker->randomElement([0, 5, 10]),
            'tax_class' => 'standard',
            'delivery_method' => $this->faker->randomElement(['in_person', 'remote', 'hybrid', 'on_site']),
            'location' => $this->faker->optional()->address(),
            'requires_materials' => $this->faker->boolean(60),
            'required_materials' => $this->faker->boolean(60) ? $this->generateRequiredMaterials() : [],
            'requires_staff' => $this->faker->boolean(80),
            'required_skills' => $this->faker->boolean(50) ? $this->generateRequiredSkills() : [],
            'min_staff' => $this->faker->numberBetween(1, 2),
            'max_staff' => $this->faker->numberBetween(2, 5),
            'images' => [],
            'featured_image' => null,
            'meta_title' => null,
            'meta_description' => null,
            'tags' => [],
            'is_featured' => $this->faker->boolean(10),
            'allow_online_booking' => $this->faker->boolean(60),
            'send_confirmation' => $this->faker->boolean(80),
            'send_reminder' => $this->faker->boolean(70),
            'reminder_hours' => $this->faker->numberBetween(1, 24),
            'sort_order' => $this->faker->numberBetween(1, 100),
            'custom_fields' => [],
            'metadata' => [],
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create service for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create service for specific category
     */
    public function forCategory($category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => is_object($category) ? $category->id : $category,
        ]);
    }

    /**
     * Create active service
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Create featured service
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Create hourly billing service
     */
    public function hourlyBilling(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_type' => 'hourly',
        ]);
    }

    /**
     * Create fixed price service
     */
    public function fixedPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_type' => 'fixed',
        ]);
    }

    /**
     * Create service requiring booking
     */
    public function requiresBooking(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_booking' => true,
            'allow_online_booking' => true,
        ]);
    }

    /**
     * Get Vietnamese service names
     */
    private function getVietnameseServiceName(): string
    {
        $services = [
            'Thi công xây dựng',
            'Vận chuyển vật liệu',
            'Tư vấn thiết kế',
            'Bảo trì hệ thống',
            'Lắp đặt thiết bị',
            'Sửa chữa công trình',
            'Kiểm định chất lượng',
            'Giám sát thi công',
            'Đo đạc địa hình',
            'Thiết kế kiến trúc',
            'Tư vấn pháp lý',
            'Quản lý dự án',
            'Đào tạo kỹ thuật',
            'Bảo dưỡng máy móc',
            'Vệ sinh công nghiệp'
        ];
        
        return $this->faker->randomElement($services);
    }

    /**
     * Generate required materials array
     */
    private function generateRequiredMaterials(): array
    {
        $materials = [
            'Xi măng',
            'Cát',
            'Đá',
            'Sắt thép',
            'Gạch',
            'Ống nước',
            'Dây điện',
            'Sơn',
            'Keo dán'
        ];
        
        return $this->faker->randomElements($materials, $this->faker->numberBetween(1, 4));
    }

    /**
     * Generate required skills array
     */
    private function generateRequiredSkills(): array
    {
        $skills = [
            'Thợ xây',
            'Thợ điện',
            'Thợ nước',
            'Thợ sơn',
            'Kỹ sư xây dựng',
            'Kiến trúc sư',
            'Thợ hàn',
            'Thợ mộc',
            'Kỹ thuật viên'
        ];
        
        return $this->faker->randomElements($skills, $this->faker->numberBetween(1, 3));
    }
}