<?php

namespace Packages\MaterialSuppliers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\MaterialSuppliers\Models\MaterialSupplier;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class MaterialSupplierFactory extends Factory
{
    protected $model = MaterialSupplier::class;

    public function definition(): array
    {
        $companies = [
            'Công ty TNHH Xi măng Hòa Bình',
            'Công ty CP Vật liệu Xây dựng Việt Nam',
            'Công ty TNHH Thương mại Đại Phát',
            'Công ty CP Xi măng Hà Tiên',
            'Công ty TNHH Sắt thép Hoa Sen',
            'Công ty CP Gạch Đồng Tâm',
            'Công ty TNHH Cát đá Minh Phú',
            'Công ty CP Vật tư Xây dựng Sài Gòn',
            'Công ty TNHH Sơn Dulux Việt Nam',
            'Công ty CP Ngói Đồng Nai',
            'Công ty TNHH Thép Pomina',
            'Công ty CP Vật liệu Xây dựng Hải Phòng'
        ];

        $names = [
            'Nguyễn Văn An', 'Trần Thị Bình', 'Lê Văn Cường', 'Phạm Thị Dung',
            'Hoàng Văn Em', 'Vũ Thị Phương', 'Đặng Văn Giang', 'Bùi Thị Hoa',
            'Dương Văn Inh', 'Ngô Thị Kim', 'Lý Văn Long', 'Tôn Thị Mai'
        ];

        $cities = [
            'TP. Hồ Chí Minh', 'Hà Nội', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ',
            'Đồng Nai', 'Bình Dương', 'Long An', 'An Giang', 'Kiên Giang'
        ];

        $addresses = [
            '123 Nguyễn Huệ', '456 Lê Lợi', '789 Trần Hưng Đạo', '321 Võ Văn Tần',
            '654 Điện Biên Phủ', '987 Cách Mạng Tháng 8', '147 Nguyễn Thị Minh Khai',
            '258 Lý Tự Trọng', '369 Hai Bà Trưng', '741 Pasteur'
        ];

        $companyName = $this->faker->randomElement($companies);
        $city = $this->faker->randomElement($cities);

        return [
            'store_id' => Store::factory(),
            'supplier_code' => 'SUP' . str_pad($this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'company_name' => $companyName,
            'contact_person' => $this->faker->randomElement($names),
            'phone' => '0' . $this->faker->numberBetween(900000000, 999999999),
            'email' => strtolower(str_replace([' ', 'TNHH', 'CP', 'Công ty'], ['', '', '', ''], $companyName)) . '@company.vn',
            'website' => 'https://' . strtolower(str_replace([' ', 'TNHH', 'CP', 'Công ty'], ['', '', '', ''], $companyName)) . '.com.vn',
            'address' => $this->faker->randomElement($addresses),
            'city' => $city,
            'province' => $city,
            'postal_code' => $this->faker->numberBetween(100000, 999999),
            'country' => 'Vietnam',
            'tax_code' => '0' . $this->faker->numberBetween(100000000, 999999999),
            'supplier_type' => $this->faker->randomElement([
                MaterialSupplier::TYPE_MANUFACTURER,
                MaterialSupplier::TYPE_DISTRIBUTOR,
                MaterialSupplier::TYPE_RETAILER,
                MaterialSupplier::TYPE_IMPORTER
            ]),
            'payment_terms' => $this->faker->randomElement([
                MaterialSupplier::PAYMENT_CASH,
                MaterialSupplier::PAYMENT_COD,
                MaterialSupplier::PAYMENT_NET_15,
                MaterialSupplier::PAYMENT_NET_30,
                MaterialSupplier::PAYMENT_NET_60
            ]),
            'credit_limit' => $this->faker->randomElement([50000000, 100000000, 200000000, 500000000, 1000000000]),
            'current_balance' => 0,
            'lead_time_days' => $this->faker->numberBetween(1, 14),
            'rating' => $this->faker->randomFloat(1, 3.0, 5.0),
            'is_active' => true,
            'is_preferred' => $this->faker->boolean(30),
            'certifications' => $this->faker->randomElements([
                'ISO 9001', 'ISO 14001', 'TCVN 2682', 'TCVN 1451', 'TCVN 7570',
                'JIS G3112', 'QUATEST', 'CR', 'Green Label'
            ], $this->faker->numberBetween(1, 3)),
            'delivery_areas' => $this->faker->randomElements([
                'TP.HCM', 'Hà Nội', 'Đà Nẵng', 'Đồng Nai', 'Bình Dương',
                'Long An', 'An Giang', 'Cần Thơ', 'Hải Phòng', 'Quảng Ninh'
            ], $this->faker->numberBetween(2, 5)),
            'notes' => $this->faker->optional()->sentence(),
            'last_order_at' => $this->faker->optional()->dateTimeBetween('-6 months', 'now'),
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

    public function preferred(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_preferred' => true,
        ]);
    }

    public function manufacturer(): static
    {
        return $this->state(fn (array $attributes) => [
            'supplier_type' => MaterialSupplier::TYPE_MANUFACTURER,
        ]);
    }

    public function distributor(): static
    {
        return $this->state(fn (array $attributes) => [
            'supplier_type' => MaterialSupplier::TYPE_DISTRIBUTOR,
        ]);
    }

    public function withHighRating(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $this->faker->randomFloat(1, 4.0, 5.0),
        ]);
    }

    public function withStore(Store $store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => $store->id,
        ]);
    }
}