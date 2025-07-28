<?php

namespace Packages\Employees\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Employees\Models\EmployeeSchedule;
use Packages\Employees\Models\Employee;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Employees\Models\EmployeeSchedule>
 */
class EmployeeScheduleFactory extends Factory
{
    protected $model = EmployeeSchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = $this->faker->time('H:i', '09:00');
        $endTime = $this->faker->time('H:i', '18:00');
        $workDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        
        return [
            'store_id' => Store::factory(),
            'employee_id' => \Packages\Employees\Database\Factories\EmployeeFactory::new(),
            'schedule_date' => $workDate,
            'day_of_week' => $workDate->format('N'), // 1 = Monday, 7 = Sunday
            'shift_type' => $this->faker->randomElement(['morning', 'afternoon', 'evening', 'night', 'full_day']),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'break_start_time' => $this->faker->optional()->time('H:i', '12:00'),
            'break_end_time' => $this->faker->optional()->time('H:i', '13:00'),
            'total_hours' => $this->calculateHours($startTime, $endTime),
            'overtime_hours' => $this->faker->optional()->randomFloat(2, 0, 4),
            'status' => $this->faker->randomElement(['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show']),
            'location' => $this->getVietnameseWorkLocation(),
            'notes' => $this->faker->optional()->sentence(),
            'is_holiday' => $this->faker->boolean(10),
            'holiday_name' => $this->faker->optional()->randomElement($this->getVietnameseHolidays()),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create schedule for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create schedule for specific employee
     */
    public function forEmployee($employee): static
    {
        return $this->state(fn (array $attributes) => [
            'employee_id' => is_object($employee) ? $employee->id : $employee,
        ]);
    }

    /**
     * Create morning shift
     */
    public function morningShift(): static
    {
        return $this->state(fn (array $attributes) => [
            'shift_type' => 'morning',
            'start_time' => '06:00',
            'end_time' => '14:00',
            'total_hours' => 8,
        ]);
    }

    /**
     * Create afternoon shift
     */
    public function afternoonShift(): static
    {
        return $this->state(fn (array $attributes) => [
            'shift_type' => 'afternoon',
            'start_time' => '14:00',
            'end_time' => '22:00',
            'total_hours' => 8,
        ]);
    }

    /**
     * Create evening shift
     */
    public function eveningShift(): static
    {
        return $this->state(fn (array $attributes) => [
            'shift_type' => 'evening',
            'start_time' => '18:00',
            'end_time' => '02:00',
            'total_hours' => 8,
        ]);
    }

    /**
     * Create full day shift
     */
    public function fullDay(): static
    {
        return $this->state(fn (array $attributes) => [
            'shift_type' => 'full_day',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'break_start_time' => '12:00',
            'break_end_time' => '13:00',
            'total_hours' => 8,
        ]);
    }

    /**
     * Create scheduled status
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
        ]);
    }

    /**
     * Create confirmed status
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    /**
     * Create completed status
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Create weekend schedule
     */
    public function weekend(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => $this->faker->randomElement([6, 7]), // Saturday or Sunday
            'overtime_hours' => $this->faker->randomFloat(2, 2, 8),
        ]);
    }

    /**
     * Create holiday schedule
     */
    public function holiday(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_holiday' => true,
            'holiday_name' => $this->faker->randomElement($this->getVietnameseHolidays()),
            'overtime_hours' => $this->faker->randomFloat(2, 4, 8),
        ]);
    }

    /**
     * Create overtime schedule
     */
    public function overtime(): static
    {
        return $this->state(fn (array $attributes) => [
            'overtime_hours' => $this->faker->randomFloat(2, 2, 6),
            'total_hours' => $this->faker->randomFloat(2, 10, 14),
        ]);
    }

    /**
     * Calculate hours between start and end time
     */
    private function calculateHours(string $startTime, string $endTime): float
    {
        $start = \DateTime::createFromFormat('H:i', $startTime);
        $end = \DateTime::createFromFormat('H:i', $endTime);
        
        if ($end < $start) {
            $end->add(new \DateInterval('P1D'));
        }
        
        $diff = $start->diff($end);
        return $diff->h + ($diff->i / 60);
    }

    /**
     * Get Vietnamese work locations
     */
    private function getVietnameseWorkLocation(): string
    {
        $locations = [
            'Cửa hàng chính',
            'Kho hàng',
            'Văn phòng',
            'Quầy bán hàng',
            'Khu vực tư vấn',
            'Phòng kế toán',
            'Khu vực giao hàng',
            'Sân bãi',
            'Phòng họp',
            'Khu vực tiếp khách',
            'Bộ phận bảo trì',
            'Khu vực an ninh',
            'Phòng nghỉ',
            'Khu vực ngoài trời',
            'Làm việc tại nhà'
        ];
        
        return $this->faker->randomElement($locations);
    }

    /**
     * Get Vietnamese holidays
     */
    private function getVietnameseHolidays(): array
    {
        return [
            'Tết Nguyên Đán',
            'Giỗ Tổ Hùng Vương',
            'Ngày Giải phóng miền Nam',
            'Quốc khánh 2/9',
            'Ngày Quốc tế Lao động',
            'Ngày Độc lập',
            'Tết Dương lịch',
            'Tết Trung thu',
            'Ngày Phụ nữ Việt Nam',
            'Ngày Nhà giáo Việt Nam',
            'Ngày Thương binh Liệt sĩ',
            'Ngày Quốc tế Phụ nữ',
            'Ngày Quốc tế Thiếu nhi',
            'Ngày Thống nhất',
            'Ngày Quân đội Nhân dân'
        ];
    }
}