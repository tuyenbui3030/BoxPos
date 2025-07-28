<?php

namespace Packages\Store\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Store\Models\Store;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Store\Models\Store>
 */
class StoreFactory extends Factory
{
    protected $model = Store::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->getVietnameseStoreName();
        
        return [
            'name' => $name,
            'slug' => Store::generateSlug($name),
            'domain' => null, // Will be set manually if needed
            'logo' => null,
            'description' => $this->getStoreDescription(),
            'address' => $this->getVietnameseAddress(),
            'phone' => $this->getVietnamesePhoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'timezone' => 'Asia/Ho_Chi_Minh',
            'currency' => 'VND',
            'language' => 'vi',
            'settings' => $this->getDefaultSettings(),
            'status' => Store::STATUS_ACTIVE,
        ];
    }

    /**
     * Get Vietnamese store names for construction materials
     */
    private function getVietnameseStoreName(): string
    {
        $storeTypes = [
            'Cửa hàng Vật liệu Xây dựng',
            'Công ty TNHH Vật liệu',
            'Cửa hàng Xi măng',
            'Đại lý Sắt thép',
            'Cửa hàng Gạch ốp lát',
            'Công ty CP Vật tư Xây dựng',
            'Cửa hàng Cát đá',
            'Đại lý Vật liệu',
        ];

        $locations = [
            'Hòa Bình', 'Đại Phát', 'Minh Phú', 'Hoa Sen', 
            'Đồng Tâm', 'Sài Gòn', 'Hà Nội', 'Đà Nẵng',
            'Cần Thơ', 'Hải Phòng', 'Bình Dương', 'Đồng Nai'
        ];

        $storeType = fake()->randomElement($storeTypes);
        $location = fake()->randomElement($locations);

        return "{$storeType} {$location}";
    }

    /**
     * Get store description
     */
    private function getStoreDescription(): string
    {
        $descriptions = [
            'Chuyên cung cấp vật liệu xây dựng chất lượng cao với giá cả cạnh tranh.',
            'Đại lý phân phối xi măng, sắt thép, gạch ốp lát và các vật liệu xây dựng khác.',
            'Cửa hàng vật liệu xây dựng uy tín với nhiều năm kinh nghiệm trong ngành.',
            'Chuyên bán buôn, bán lẻ vật liệu xây dựng cho các công trình lớn nhỏ.',
            'Cung cấp đầy đủ vật tư xây dựng từ cơ bản đến cao cấp.',
        ];

        return fake()->randomElement($descriptions);
    }

    /**
     * Get Vietnamese addresses
     */
    private function getVietnameseAddress(): string
    {
        $streets = [
            'Nguyễn Huệ', 'Lê Lợi', 'Trần Hưng Đạo', 'Võ Văn Tần',
            'Điện Biên Phủ', 'Cách Mạng Tháng 8', 'Nguyễn Thị Minh Khai',
            'Lý Tự Trọng', 'Hai Bà Trưng', 'Nguyễn Du'
        ];

        $districts = [
            'Quận 1', 'Quận 3', 'Quận 5', 'Quận 10',
            'Quận Bình Thạnh', 'Quận Tân Bình', 'Quận Phú Nhuận',
            'Quận Gò Vấp', 'Quận Thủ Đức'
        ];

        $cities = [
            'TP.HCM', 'Hà Nội', 'Đà Nẵng', 'Cần Thơ', 'Hải Phòng'
        ];

        $number = fake()->numberBetween(1, 999);
        $street = fake()->randomElement($streets);
        $district = fake()->randomElement($districts);
        $city = fake()->randomElement($cities);

        return "{$number} {$street}, {$district}, {$city}";
    }

    /**
     * Get Vietnamese phone number
     */
    private function getVietnamesePhoneNumber(): string
    {
        $prefixes = ['028', '024', '0236', '0292', '0225']; // Ho Chi Minh, Hanoi, Da Nang, Can Tho, Hai Phong
        $mobilePrefix = ['090', '091', '094', '083', '084', '085', '088'];
        
        if (fake()->boolean(70)) {
            // Mobile number
            $prefix = fake()->randomElement($mobilePrefix);
            $number = fake()->numerify('#######');
            return "{$prefix}{$number}";
        } else {
            // Landline number
            $prefix = fake()->randomElement($prefixes);
            $number = fake()->numerify('#######');
            return "{$prefix}.{$number}";
        }
    }

    /**
     * Get default store settings
     */
    private function getDefaultSettings(): array
    {
        return [
            'business_hours' => [
                'monday' => ['open' => '08:00', 'close' => '18:00'],
                'tuesday' => ['open' => '08:00', 'close' => '18:00'],
                'wednesday' => ['open' => '08:00', 'close' => '18:00'],
                'thursday' => ['open' => '08:00', 'close' => '18:00'],
                'friday' => ['open' => '08:00', 'close' => '18:00'],
                'saturday' => ['open' => '08:00', 'close' => '17:00'],
                'sunday' => ['open' => '09:00', 'close' => '16:00'],
            ],
            'tax_settings' => [
                'vat_rate' => 10,
                'tax_number' => fake()->numerify('##########'),
            ],
            'inventory_settings' => [
                'low_stock_threshold' => 10,
                'auto_reorder' => false,
                'track_serial_numbers' => false,
            ],
            'notification_settings' => [
                'email_notifications' => true,
                'sms_notifications' => false,
                'low_stock_alerts' => true,
            ],
            'display_settings' => [
                'theme' => 'light',
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i',
                'decimal_places' => 0,
            ],
        ];
    }

    /**
     * Indicate that the store should be inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Store::STATUS_INACTIVE,
        ]);
    }

    /**
     * Indicate that the store should be suspended
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Store::STATUS_SUSPENDED,
        ]);
    }

    /**
     * Set a custom domain for the store
     */
    public function withDomain(string $domain): static
    {
        return $this->state(fn (array $attributes) => [
            'domain' => $domain,
        ]);
    }

    /**
     * Create a store with minimal settings (for testing)
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
            'logo' => null,
            'settings' => [
                'business_hours' => [
                    'monday' => ['open' => '08:00', 'close' => '18:00'],
                ],
                'tax_settings' => [
                    'vat_rate' => 10,
                ],
            ],
        ]);
    }
}