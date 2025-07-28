<?php

namespace Packages\Employees\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Employees\Models\Position;
use Packages\Employees\Models\Department;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Employees\Models\Position>
 */
class PositionFactory extends Factory
{
    protected $model = Position::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseSalary = $this->faker->randomFloat(2, 5000000, 30000000);
        
        return [
            'store_id' => Store::factory(),
            'department_id' => \Packages\Employees\Database\Factories\DepartmentFactory::new(),
            'code' => $this->faker->unique()->regexify('POS[0-9]{3}'),
            'title' => $this->getVietnamesePositionName(),
            'description' => $this->faker->optional()->paragraph(),
            'level' => $this->faker->randomElement(['junior', 'senior', 'lead', 'manager', 'director']),
            'min_salary' => $baseSalary * 0.8,
            'max_salary' => $baseSalary * 1.5,
            'requirements' => implode("\n", $this->getVietnameseRequirements()),
            'responsibilities' => implode("\n", $this->getVietnameseResponsibilities()),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'metadata' => [],
        ];
    }

    /**
     * Create position for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create position for specific department
     */
    public function forDepartment($department): static
    {
        return $this->state(fn (array $attributes) => [
            'department_id' => is_object($department) ? $department->id : $department,
        ]);
    }

    /**
     * Create manager level position
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 'manager',
            'min_salary' => $this->faker->randomFloat(2, 15000000, 30000000),
            'max_salary' => $this->faker->randomFloat(2, 30000000, 50000000),
        ]);
    }

    /**
     * Create junior level position
     */
    public function junior(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 'junior',
            'min_salary' => $this->faker->randomFloat(2, 5000000, 8000000),
            'max_salary' => $this->faker->randomFloat(2, 8000000, 12000000),
        ]);
    }

    /**
     * Get Vietnamese position names
     */
    private function getVietnamesePositionName(): string
    {
        $positions = [
            'Nhân viên bán hàng',
            'Trưởng phòng bán hàng',
            'Nhân viên kho',
            'Thủ kho',
            'Kế toán viên',
            'Trưởng phòng kế toán',
            'Nhân viên nhân sự',
            'Trưởng phòng nhân sự',
            'Giám đốc',
            'Phó giám đốc',
            'Nhân viên marketing',
            'Trưởng phòng marketing',
            'Kỹ thuật viên',
            'Trưởng phòng kỹ thuật',
            'Tư vấn viên',
            'Nhân viên CSKH',
            'Lái xe',
            'Bảo vệ',
            'Nhân viên vệ sinh',
            'Thực tập sinh'
        ];
        
        return $this->faker->randomElement($positions);
    }

    /**
     * Get Vietnamese benefits
     */
    private function getVietnameseBenefits(): array
    {
        $benefits = [
            'Bảo hiểm xã hội',
            'Bảo hiểm y tế',
            'Bảo hiểm thất nghiệp',
            'Phụ cấp ăn trưa',
            'Phụ cấp xăng xe',
            'Phụ cấp điện thoại',
            'Thưởng tháng 13',
            'Thưởng hiệu suất',
            'Du lịch hàng năm',
            'Khám sức khỏe định kỳ',
            'Đào tạo nâng cao',
            'Nghỉ phép có lương'
        ];
        
        return $this->faker->randomElements($benefits, $this->faker->numberBetween(3, 8));
    }

    /**
     * Get Vietnamese requirements
     */
    private function getVietnameseRequirements(): array
    {
        $requirements = [
            'Tốt nghiệp THPT trở lên',
            'Có kinh nghiệm làm việc',
            'Kỹ năng giao tiếp tốt',
            'Sử dụng máy tính văn phòng',
            'Có trách nhiệm trong công việc',
            'Làm việc nhóm tốt',
            'Chịu được áp lực công việc',
            'Có bằng lái xe',
            'Biết tiếng Anh cơ bản',
            'Có kinh nghiệm bán hàng',
            'Thành thạo Excel',
            'Có kinh nghiệm quản lý'
        ];
        
        return $this->faker->randomElements($requirements, $this->faker->numberBetween(2, 6));
    }

    /**
     * Get Vietnamese responsibilities
     */
    private function getVietnameseResponsibilities(): array
    {
        $responsibilities = [
            'Tư vấn và bán hàng cho khách hàng',
            'Quản lý kho hàng và kiểm soát tồn kho',
            'Lập báo cáo bán hàng hàng ngày',
            'Chăm sóc khách hàng sau bán hàng',
            'Thực hiện các công việc được giao',
            'Phối hợp với các phòng ban khác',
            'Đảm bảo an toàn lao động',
            'Tuân thủ quy định của công ty',
            'Báo cáo công việc với cấp trên',
            'Tham gia đào tạo và học tập'
        ];
        
        return $this->faker->randomElements($responsibilities, $this->faker->numberBetween(3, 7));
    }
}