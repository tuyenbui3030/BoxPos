<?php

namespace Packages\MaterialSuppliers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\MaterialSuppliers\Models\SupplierContact;
use Packages\MaterialSuppliers\Models\MaterialSupplier;

class SupplierContactFactory extends Factory
{
    protected $model = SupplierContact::class;

    public function definition(): array
    {
        $names = [
            'Nguyễn Văn An', 'Trần Thị Bình', 'Lê Văn Cường', 'Phạm Thị Dung',
            'Hoàng Văn Em', 'Vũ Thị Phương', 'Đặng Văn Giang', 'Bùi Thị Hoa',
            'Dương Văn Inh', 'Ngô Thị Kim', 'Lý Văn Long', 'Tôn Thị Mai',
            'Đinh Văn Nam', 'Chu Thị Oanh', 'Võ Văn Phúc', 'Đỗ Thị Quỳnh'
        ];

        $positions = [
            'Giám đốc Kinh doanh', 'Trưởng phòng Bán hàng', 'Nhân viên Kinh doanh',
            'Giám đốc Kỹ thuật', 'Trưởng phòng Kỹ thuật', 'Kỹ sư Kỹ thuật',
            'Giám đốc Tài chính', 'Trưởng phòng Kế toán', 'Nhân viên Kế toán',
            'Giám đốc Điều hành', 'Trưởng phòng Hành chính', 'Thư ký'
        ];

        $departments = [
            'Kinh doanh', 'Kỹ thuật', 'Tài chính', 'Kế toán', 
            'Hành chính', 'Nhân sự', 'Marketing', 'Vận chuyển'
        ];

        $responsibilities = [
            'Tư vấn sản phẩm', 'Báo giá', 'Đàm phán hợp đồng', 'Theo dõi đơn hàng',
            'Hỗ trợ kỹ thuật', 'Giải quyết khiếu nại', 'Chăm sóc khách hàng',
            'Vận chuyển giao hàng', 'Thanh toán', 'Bảo hành'
        ];

        return [
            'supplier_id' => MaterialSupplier::factory(),
            'name' => $this->faker->randomElement($names),
            'position' => $this->faker->randomElement($positions),
            'department' => $this->faker->randomElement($departments),
            'phone' => '028' . $this->faker->numberBetween(10000000, 99999999),
            'mobile' => '0' . $this->faker->numberBetween(900000000, 999999999),
            'email' => $this->faker->unique()->safeEmail(),
            'fax' => $this->faker->optional()->phoneNumber(),
            'is_primary' => false,
            'is_active' => true,
            'responsibilities' => $this->faker->randomElements($responsibilities, $this->faker->numberBetween(2, 4)),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function forSupplier(MaterialSupplier $supplier): static
    {
        return $this->state(fn (array $attributes) => [
            'supplier_id' => $supplier->id,
        ]);
    }

    public function sales(): static
    {
        return $this->state(fn (array $attributes) => [
            'department' => 'Kinh doanh',
            'position' => $this->faker->randomElement([
                'Giám đốc Kinh doanh', 'Trưởng phòng Bán hàng', 'Nhân viên Kinh doanh'
            ]),
            'responsibilities' => ['Tư vấn sản phẩm', 'Báo giá', 'Đàm phán hợp đồng'],
        ]);
    }

    public function technical(): static
    {
        return $this->state(fn (array $attributes) => [
            'department' => 'Kỹ thuật',
            'position' => $this->faker->randomElement([
                'Giám đốc Kỹ thuật', 'Trưởng phòng Kỹ thuật', 'Kỹ sư Kỹ thuật'
            ]),
            'responsibilities' => ['Hỗ trợ kỹ thuật', 'Tư vấn kỹ thuật', 'Giải quyết khiếu nại'],
        ]);
    }
}