<?php

namespace Packages\Loyalty\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Loyalty\Models\LoyaltyProgram;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Loyalty\Models\LoyaltyProgram>
 */
class LoyaltyProgramFactory extends Factory
{
    protected $model = LoyaltyProgram::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'code' => $this->faker->unique()->regexify('LP[0-9]{6}'),
            'name' => $this->getVietnameseProgramName(),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['points', 'tiers', 'cashback', 'visits']),
            'status' => $this->faker->randomElement(['active', 'inactive', 'draft']),
            'auto_enrollment' => $this->faker->boolean(70),
            'earn_rate' => $this->faker->randomFloat(4, 0.01, 0.1), // 1-10% earning rate
            'redeem_rate' => $this->faker->randomFloat(4, 0.8, 1.0), // 80-100% redemption rate
            'min_points_to_redeem' => $this->faker->numberBetween(100, 1000),
            'max_points_per_transaction' => $this->faker->optional()->numberBetween(100, 1000),
            'points_expiry_days' => $this->faker->optional()->numberBetween(180, 730),
            'tier_config' => $this->getVietnameseMembershipTiers(),
            'tier_based_earning' => $this->faker->boolean(30),
            'tier_benefits' => $this->getVietnameseTierBenefits(),
            'cashback_rate' => $this->faker->optional()->randomFloat(4, 0.01, 0.05),
            'min_cashback_amount' => $this->faker->optional()->randomFloat(2, 10000, 50000),
            'max_cashback_amount' => $this->faker->optional()->randomFloat(2, 500000, 2000000),
            'visits_for_reward' => $this->faker->optional()->numberBetween(5, 20),
            'visit_reward_amount' => $this->faker->optional()->randomFloat(2, 50000, 200000),
            'start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'end_date' => $this->faker->optional()->dateTimeBetween('now', '+2 years'),
            'eligible_customer_groups' => [],
            'min_purchase_amount' => $this->faker->randomFloat(2, 50000, 500000),
            'excluded_products' => [],
            'excluded_categories' => [],
            'earning_channels' => ['online', 'in_store'],
            'redemption_channels' => ['online', 'in_store'],
            'bonus_events' => [],
            'multiplier_rules' => [],
            'birthday_bonus' => $this->faker->boolean(80),
            'birthday_bonus_points' => $this->faker->optional()->numberBetween(50, 200),
            'welcome_bonus' => $this->faker->boolean(90),
            'welcome_bonus_points' => $this->faker->optional()->numberBetween(50, 500),
            'referral_bonus' => $this->faker->boolean(60),
            'referral_bonus_points' => $this->faker->optional()->numberBetween(100, 1000),
            'notification_settings' => [],
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create program for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create active program
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Create points-based program
     */
    public function pointsBased(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'points',
            'earn_rate' => $this->faker->randomFloat(4, 0.01, 0.05),
            'redeem_rate' => 1.0,
        ]);
    }

    /**
     * Create tier-based program
     */
    public function tierBased(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'tiers',
            'tier_config' => $this->getVietnameseMembershipTiers(),
            'tier_based_earning' => true,
        ]);
    }

    /**
     * Create cashback program
     */
    public function cashback(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'cashback',
            'cashback_rate' => $this->faker->randomFloat(4, 0.02, 0.1),
        ]);
    }

    /**
     * Create program with auto enrollment
     */
    public function autoEnrollment(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_enrollment' => true,
        ]);
    }

    /**
     * Get Vietnamese program names
     */
    private function getVietnameseProgramName(): string
    {
        $names = [
            'Chương trình Khách hàng Thân thiết',
            'Hội viên VIP BoxPos',
            'Tích điểm Đổi quà',
            'Khách hàng Ưu tiên',
            'Thành viên Vàng',
            'Club Xây dựng',
            'Hội viên Đặc biệt',
            'Chương trình Tích lũy',
            'Khách hàng Trung thành',
            'VIP Membership'
        ];
        
        return $this->faker->randomElement($names);
    }

    /**
     * Get Vietnamese membership tiers
     */
    private function getVietnameseMembershipTiers(): array
    {
        return [
            [
                'name' => 'Đồng',
                'min_points' => 0,
                'max_points' => 999,
                'min_spending' => 0,
                'color' => '#CD7F32'
            ],
            [
                'name' => 'Bạc',
                'min_points' => 1000,
                'max_points' => 4999,
                'min_spending' => 5000000,
                'color' => '#C0C0C0'
            ],
            [
                'name' => 'Vàng',
                'min_points' => 5000,
                'max_points' => 9999,
                'min_spending' => 20000000,
                'color' => '#FFD700'
            ],
            [
                'name' => 'Kim cương',
                'min_points' => 10000,
                'max_points' => null,
                'min_spending' => 50000000,
                'color' => '#B9F2FF'
            ]
        ];
    }

    /**
     * Get Vietnamese tier benefits
     */
    private function getVietnameseTierBenefits(): array
    {
        return [
            'Đồng' => [
                'Tích điểm cơ bản 1%',
                'Sinh nhật tặng 50 điểm',
                'Thông báo khuyến mãi'
            ],
            'Bạc' => [
                'Tích điểm 1.5%',
                'Sinh nhật tặng 100 điểm',
                'Ưu tiên hỗ trợ',
                'Giảm giá 5% dịch vụ vận chuyển'
            ],
            'Vàng' => [
                'Tích điểm 2%',
                'Sinh nhật tặng 200 điểm',
                'Tư vấn miễn phí',
                'Miễn phí vận chuyển',
                'Ưu tiên thanh toán'
            ],
            'Kim cương' => [
                'Tích điểm 3%',
                'Sinh nhật tặng 500 điểm',
                'Tư vấn chuyên sâu',
                'Miễn phí tất cả dịch vụ',
                'Quản lý tài khoản riêng',
                'Ưu đãi đặc biệt'
            ]
        ];
    }

    /**
     * Get Vietnamese terms and conditions
     */
    private function getVietnameseTermsAndConditions(): array
    {
        return [
            'Chương trình áp dụng cho tất cả khách hàng mua hàng tại cửa hàng',
            'Điểm tích lũy có thời hạn sử dụng 12 tháng kể từ ngày tích',
            'Điểm không được chuyển nhượng cho người khác',
            'Khách hàng cần xuất trình thẻ thành viên khi mua hàng',
            'Chương trình có thể thay đổi mà không cần báo trước',
            'Điểm chỉ được tích khi thanh toán đầy đủ',
            'Không áp dụng tích điểm cho hàng khuyến mãi',
            'Quyết định của cửa hàng là quyết định cuối cùng'
        ];
    }
}