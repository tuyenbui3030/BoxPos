<?php

namespace Packages\Employees\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Employees\Models\Department;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Employees\Models\Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'code' => $this->faker->unique()->regexify('DEPT[0-9]{3}'),
            'name' => $this->getVietnameseDepartmentName(),
            'description' => $this->faker->optional()->paragraph(),
            'manager_id' => null, // Will be set after employees are created
            'parent_id' => null,
            'location' => $this->faker->optional()->randomElement(['Tầng 1', 'Tầng 2', 'Tầng 3', 'Văn phòng A', 'Văn phòng B']),
            'max_employees' => $this->faker->optional()->numberBetween(5, 50),
            'metadata' => [],
            'budget' => $this->faker->randomFloat(2, 10000000, 100000000),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),

        ];
    }

    /**
     * Create department for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create active department
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create department with manager
     */
    public function withManager($manager): static
    {
        return $this->state(fn (array $attributes) => [
            'manager_id' => is_object($manager) ? $manager->id : $manager,
        ]);
    }

    /**
     * Get Vietnamese department names
     */
    private function getVietnameseDepartmentName(): string
    {
        $departments = [
            'Phòng Bán hàng',
            'Phòng Kho vận',
            'Phòng Kế toán',
            'Phòng Nhân sự',
            'Phòng Quản lý',
            'Phòng Marketing',
            'Phòng Kỹ thuật',
            'Phòng Tư vấn',
            'Phòng Dịch vụ khách hàng',
            'Phòng Vận chuyển',
            'Phòng Kiểm soát chất lượng',
            'Phòng IT',
            'Phòng Pháp chế',
            'Phòng Đào tạo',
            'Phòng An toàn lao động'
        ];
        
        return $this->faker->randomElement($departments);
    }
}