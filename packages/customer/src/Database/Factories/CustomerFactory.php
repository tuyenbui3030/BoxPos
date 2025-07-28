<?php

namespace Packages\Customer\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\User\Models\User;
use Packages\Customer\Models\Customer;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Customer\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalSales = $this->faker->randomFloat(2, 0, 50000);
        $returns = $this->faker->randomFloat(2, 0, $totalSales * 0.1);
        $debt = $this->faker->boolean(30) ? $this->faker->randomFloat(2, 0, 5000) : 0;
        
        return [
            'customer_code' => Customer::generateCustomerCode(),
            'customer_name' => $this->getVietnameseName(),
            'phone_number' => $this->generateVietnamesePhoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->getVietnameseAddress(),
            'customer_type' => $this->faker->randomElement(['individual', 'company']),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'birthday' => $this->faker->dateTimeBetween('-80 years', '-18 years'),
            'customer_group' => $this->faker->randomElement(['VIP', 'Thường', 'Mới', 'Sỉ', 'Lẻ', 'Đại lý', 'Nhà thầu']),
            'current_debt' => $debt,
            'total_sales' => $totalSales,
            'total_sales_minus_returns' => $totalSales - $returns,
            'last_transaction_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'created_by' => User::factory(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the customer has debt.
     */
    public function withDebt(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_debt' => $this->faker->randomFloat(2, 100, 5000),
        ]);
    }

    /**
     * Indicate that the customer is a company.
     */
    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_type' => 'company',
            'gender' => null,
        ]);
    }

    /**
     * Indicate that the customer is VIP.
     */
    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_group' => 'VIP',
            'total_sales' => $this->faker->randomFloat(2, 10000, 100000),
        ]);
    }

    /**
     * Create customer for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create customer with Vietnamese company name
     */
    public function vietnameseCompany(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_name' => $this->getVietnameseCompanyName(),
            'customer_type' => 'company',
            'gender' => null,
            'birthday' => null,
            'email' => $this->faker->unique()->companyEmail(),
            'total_sales' => $this->faker->randomFloat(2, 10000, 200000),
            'customer_group' => $this->faker->randomElement(['VIP', 'Sỉ', 'Đại lý', 'Nhà thầu']),
        ]);
    }

    /**
     * Get Vietnamese individual names
     */
    private function getVietnameseName(): string
    {
        $names = [
            'Nguyễn Văn An', 'Trần Thị Bình', 'Lê Văn Cường', 'Phạm Thị Dung',
            'Hoàng Văn Em', 'Vũ Thị Phương', 'Đặng Văn Giang', 'Bùi Thị Hoa',
            'Dương Văn Inh', 'Ngô Thị Kim', 'Lý Văn Long', 'Tôn Thị Mai',
            'Đinh Văn Nam', 'Chu Thị Oanh', 'Võ Văn Phúc', 'Đỗ Thị Quỳnh',
            'Nguyễn Thị Lan', 'Trần Văn Minh', 'Lê Thị Nga', 'Phạm Văn Ơn',
            'Hoàng Thị Phúc', 'Vũ Văn Quang', 'Đặng Thị Rượu', 'Bùi Văn Sơn'
        ];
        
        return $this->faker->randomElement($names);
    }

    /**
     * Get Vietnamese company names
     */
    private function getVietnameseCompanyName(): string
    {
        $companies = [
            'Công ty TNHH Xây dựng Hòa Bình',
            'Công ty CP Vật liệu Xây dựng Việt Nam',
            'Công ty TNHH Thương mại Đại Phát',
            'Công ty CP Xi măng Hà Tiên',
            'Công ty TNHH Sắt thép Hoa Sen',
            'Công ty CP Gạch Đồng Tâm',
            'Công ty TNHH Cát đá Minh Phú',
            'Công ty CP Vật tư Xây dựng Sài Gòn',
            'Công ty TNHH Xây dựng Thành Đạt',
            'Công ty CP Vật liệu Hòa Phát'
        ];
        
        return $this->faker->randomElement($companies);
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
            '741 Nguyễn Trãi, Quận 5, TP.HCM'
        ];
        
        return $this->faker->randomElement($addresses);
    }

    /**
     * Generate realistic Vietnamese phone numbers
     */
    private function generateVietnamesePhoneNumber(): string
    {
        $prefixes = ['090', '091', '094', '083', '084', '085', '081', '082', '032', '033', '034', '035', '036', '037', '038', '039'];
        $prefix = $this->faker->randomElement($prefixes);
        $suffix = $this->faker->numerify('#######');
        
        return $prefix . $suffix;
    }
}
