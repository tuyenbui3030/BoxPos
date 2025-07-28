<?php

namespace Packages\Employees\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Employees\Models\Employee;
use Packages\Employees\Models\Department;
use Packages\Employees\Models\Position;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Employees\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hireDate = $this->faker->dateTimeBetween('-5 years', 'now');
        $baseSalary = $this->faker->randomFloat(2, 5000000, 25000000);
        
        return [
            'store_id' => Store::factory(),
            'department_id' => \Packages\Employees\Database\Factories\DepartmentFactory::new(),
            'position_id' => \Packages\Employees\Database\Factories\PositionFactory::new(),
            'user_id' => User::factory(),
            'employee_code' => $this->faker->unique()->regexify('EMP[0-9]{6}'),
            'full_name' => $this->getVietnameseName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->generateVietnamesePhoneNumber(),
            'address' => $this->getVietnameseAddress(),
            'date_of_birth' => $this->faker->dateTimeBetween('-60 years', '-18 years'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'id_number' => $this->generateVietnameseIdNumber(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'emergency_contact_name' => $this->getVietnameseName(),
            'emergency_contact_phone' => $this->generateVietnamesePhoneNumber(),
            'hire_date' => $hireDate,
            'probation_end_date' => $this->faker->optional()->dateTimeBetween($hireDate, '+3 months'),
            'employment_type' => $this->faker->randomElement(['full_time', 'part_time', 'contract', 'intern', 'freelance']),
            'contract_type' => $this->faker->randomElement(['permanent', 'fixed_term', 'probation', 'seasonal']),
            'contract_start_date' => $hireDate,
            'contract_end_date' => $this->faker->optional()->dateTimeBetween($hireDate, '+2 years'),
            'work_location' => $this->faker->optional()->randomElement(['Văn phòng chính', 'Chi nhánh', 'Làm việc từ xa']),
            'status' => $this->faker->randomElement(['active', 'inactive', 'terminated', 'on_leave', 'suspended']),
            'basic_salary' => $baseSalary,
            'salary_grade' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'pay_frequency' => $this->faker->randomElement(['hourly', 'daily', 'weekly', 'monthly']),
            'currency' => 'VND',
            'salary_effective_date' => $hireDate,
            'working_hours' => 8,
            'weekly_hours' => 40,
            'break_time' => json_encode(['start' => '12:00', 'end' => '13:00']),
            'health_insurance' => $this->faker->boolean(90),
            'social_insurance' => $this->faker->boolean(95),
            'vacation_days' => $this->faker->numberBetween(12, 20),
            'sick_leave_days' => $this->faker->numberBetween(5, 10),
            'tax_id' => $this->faker->optional()->numerify('##########'),
            'dependents' => $this->faker->numberBetween(0, 4),
            'skills' => json_encode($this->getVietnameseSkills()),
            'certifications' => json_encode($this->faker->optional()->randomElements([
                'Chứng chỉ tin học văn phòng',
                'Chứng chỉ tiếng Anh',
                'Bằng lái xe B2',
                'Chứng chỉ kế toán',
                'Chứng chỉ an toàn lao động'
            ], $this->faker->numberBetween(0, 3))),
            'metadata' => json_encode([]),
            'notes' => $this->faker->optional()->paragraph(),
            'termination_date' => null,
            'termination_reason' => null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create employee for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create employee for specific department
     */
    public function forDepartment($department): static
    {
        return $this->state(fn (array $attributes) => [
            'department_id' => is_object($department) ? $department->id : $department,
        ]);
    }

    /**
     * Create employee for specific position
     */
    public function forPosition($position): static
    {
        return $this->state(fn (array $attributes) => [
            'position_id' => is_object($position) ? $position->id : $position,
        ]);
    }

    /**
     * Create active employee
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Create terminated employee
     */
    public function terminated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'terminated',
            'termination_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'termination_reason' => $this->faker->randomElement([
                'Nghỉ việc theo nguyện vọng',
                'Hết hạn hợp đồng',
                'Vi phạm nội quy',
                'Cắt giảm nhân sự',
                'Chuyển công tác'
            ]),
        ]);
    }

    /**
     * Create manager employee
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'basic_salary' => $this->faker->randomFloat(2, 15000000, 50000000),
            'contract_type' => 'full_time',
        ]);
    }

    /**
     * Get Vietnamese names
     */
    private function getVietnameseName(): string
    {
        $names = [
            'Nguyễn Văn An', 'Trần Thị Bình', 'Lê Văn Cường', 'Phạm Thị Dung',
            'Hoàng Văn Em', 'Vũ Thị Phương', 'Đặng Văn Giang', 'Bùi Thị Hoa',
            'Dương Văn Inh', 'Ngô Thị Kim', 'Lý Văn Long', 'Tôn Thị Mai',
            'Đinh Văn Nam', 'Chu Thị Oanh', 'Võ Văn Phúc', 'Đỗ Thị Quỳnh',
            'Nguyễn Thị Lan', 'Trần Văn Minh', 'Lê Thị Nga', 'Phạm Văn Ơn',
            'Hoàng Thị Phúc', 'Vũ Văn Quang', 'Đặng Thị Rượu', 'Bùi Văn Sơn',
            'Dương Thị Tâm', 'Ngô Văn Uy', 'Lý Thị Vân', 'Tôn Văn Xuân'
        ];
        
        return $this->faker->randomElement($names);
    }

    /**
     * Get Vietnamese addresses
     */
    private function getVietnameseAddress(): string
    {
        $addresses = [
            '123 Nguyễn Huệ, Quận 1, TP.HCM',
            '456 Lê Lợi, Quận 3, TP.HCM',
            '789 Trần Hưng Đạo, Quận 5, TP.HCM',
            '321 Võ Văn Tần, Quận 3, TP.HCM',
            '654 Điện Biên Phủ, Quận Bình Thạnh, TP.HCM',
            '987 Cách Mạng Tháng 8, Quận 10, TP.HCM',
            '147 Nguyễn Thị Minh Khai, Quận 1, TP.HCM',
            '258 Lý Tự Trọng, Quận 1, TP.HCM',
            '369 Hai Bà Trưng, Quận 1, TP.HCM',
            '741 Nguyễn Trãi, Quận 5, TP.HCM',
            '852 Lê Văn Sỹ, Quận 3, TP.HCM',
            '963 Phan Xích Long, Quận Phú Nhuận, TP.HCM'
        ];
        
        return $this->faker->randomElement($addresses);
    }

    /**
     * Generate Vietnamese phone numbers
     */
    private function generateVietnamesePhoneNumber(): string
    {
        $prefixes = ['090', '091', '094', '083', '084', '085', '081', '082', '032', '033', '034', '035', '036', '037', '038', '039'];
        $prefix = $this->faker->randomElement($prefixes);
        $suffix = $this->faker->numerify('#######');
        
        return $prefix . $suffix;
    }

    /**
     * Generate Vietnamese ID numbers
     */
    private function generateVietnameseIdNumber(): string
    {
        return $this->faker->numerify('############');
    }

    /**
     * Get Vietnamese ID issued places
     */
    private function getVietnameseIdIssuedPlace(): string
    {
        $places = [
            'CA TP. Hồ Chí Minh',
            'CA Hà Nội',
            'CA Đà Nẵng',
            'CA Hải Phòng',
            'CA Cần Thơ',
            'CA Đồng Nai',
            'CA Bình Dương',
            'CA Long An',
            'CA An Giang',
            'CA Kiên Giang',
            'CA Bà Rịa - Vũng Tàu',
            'CA Tây Ninh'
        ];
        
        return $this->faker->randomElement($places);
    }

    /**
     * Get Vietnamese bank names
     */
    private function getVietnameseBankName(): string
    {
        $banks = [
            'Vietcombank',
            'BIDV',
            'VietinBank',
            'Agribank',
            'Techcombank',
            'MB Bank',
            'ACB',
            'VPBank',
            'TPBank',
            'Sacombank',
            'HDBank',
            'OCB'
        ];
        
        return $this->faker->randomElement($banks);
    }

    /**
     * Get Vietnamese skills
     */
    private function getVietnameseSkills(): array
    {
        $skills = [
            'Tin học văn phòng',
            'Tiếng Anh giao tiếp',
            'Kỹ năng bán hàng',
            'Kỹ năng giao tiếp',
            'Lái xe máy',
            'Lái xe ô tô',
            'Kế toán',
            'Quản lý kho',
            'Chăm sóc khách hàng',
            'Marketing',
            'Thiết kế đồ họa',
            'Quản lý nhân sự'
        ];
        
        return $this->faker->randomElements($skills, $this->faker->numberBetween(2, 6));
    }
}