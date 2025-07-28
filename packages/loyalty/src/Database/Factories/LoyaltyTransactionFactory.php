<?php

namespace Packages\Loyalty\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Loyalty\Models\LoyaltyTransaction;
use Packages\Loyalty\Models\LoyaltyProgram;
use Packages\Customer\Models\Customer;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Loyalty\Models\LoyaltyTransaction>
 */
class LoyaltyTransactionFactory extends Factory
{
    protected $model = LoyaltyTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $transactionType = $this->faker->randomElement(['earn', 'redeem', 'expire', 'adjust', 'bonus']);
        $points = $this->faker->numberBetween(10, 500);
        
        return [
            'store_id' => Store::factory(),
            'loyalty_membership_id' => null, // Will be set by seeder
            'customer_id' => Customer::factory(),
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'transaction_type' => $transactionType,
            'points' => $transactionType === 'redeem' ? -$points : $points,
            'balance_before' => $this->faker->numberBetween(0, 2000),
            'balance_after' => function (array $attributes) {
                return $attributes['balance_before'] + $attributes['points'];
            },
            'order_number' => $this->faker->optional()->regexify('ORD[0-9]{6}'),
            'order_id' => $this->faker->optional()->numberBetween(1, 1000),
            'order_type' => $this->faker->optional()->randomElement(['sales_order', 'invoice', 'return']),
            'order_amount' => $this->faker->optional()->randomFloat(2, 100000, 5000000),
            'reference_type' => $this->faker->optional()->randomElement(['invoice', 'sales_order', 'manual', 'birthday', 'referral']),
            'reference_id' => $this->faker->optional()->numberBetween(1, 1000),
            'description' => $this->getVietnameseTransactionDescription($transactionType),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('+6 months', '+2 years'),
            'status' => $this->faker->randomElement(['pending', 'completed', 'cancelled', 'expired']),
            'processed_by' => User::factory(),
            'processed_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'notes' => $this->faker->optional()->sentence(),
            'metadata' => [],
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create transaction for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create transaction for specific program
     */
    public function forMembership($membership): static
    {
        return $this->state(fn (array $attributes) => [
            'loyalty_membership_id' => is_object($membership) ? $membership->id : $membership,
        ]);
    }

    /**
     * Create transaction for specific customer
     */
    public function forCustomer($customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => is_object($customer) ? $customer->id : $customer,
        ]);
    }

    /**
     * Create earning transaction
     */
    public function earn(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'earn',
            'points' => $this->faker->numberBetween(10, 500),
            'description' => $this->getVietnameseEarnDescription(),
            'earning_rate' => $this->faker->randomFloat(4, 0.01, 0.1),
            'reference_amount' => $this->faker->randomFloat(2, 100000, 5000000),
        ]);
    }

    /**
     * Create redemption transaction
     */
    public function redeem(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'redeem',
            'points' => -$this->faker->numberBetween(50, 1000),
            'description' => $this->getVietnameseRedeemDescription(),
        ]);
    }

    /**
     * Create bonus transaction
     */
    public function bonus(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'bonus',
            'points' => $this->faker->numberBetween(50, 500),
            'description' => $this->getVietnameseBonusDescription(),
        ]);
    }

    /**
     * Create expiry transaction
     */
    public function expire(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'expire',
            'points' => -$this->faker->numberBetween(10, 200),
            'description' => 'Điểm hết hạn sử dụng',
        ]);
    }

    /**
     * Create adjustment transaction
     */
    public function adjust(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'adjust',
            'points' => $this->faker->randomElement([-100, -50, 50, 100, 200]),
            'description' => $this->getVietnameseAdjustDescription(),
        ]);
    }

    /**
     * Create processed transaction
     */
    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_processed' => true,
            'processed_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Get Vietnamese transaction descriptions
     */
    private function getVietnameseTransactionDescription(string $type): string
    {
        switch ($type) {
            case 'earn':
                return $this->getVietnameseEarnDescription();
            case 'redeem':
                return $this->getVietnameseRedeemDescription();
            case 'bonus':
                return $this->getVietnameseBonusDescription();
            case 'expire':
                return 'Điểm hết hạn sử dụng';
            case 'adjust':
                return $this->getVietnameseAdjustDescription();
            default:
                return 'Giao dịch điểm thưởng';
        }
    }

    /**
     * Get Vietnamese earn descriptions
     */
    private function getVietnameseEarnDescription(): string
    {
        $descriptions = [
            'Tích điểm từ mua hàng',
            'Điểm thưởng từ hóa đơn',
            'Tích điểm từ dịch vụ',
            'Điểm từ giao dịch mua sắm',
            'Tích lũy từ thanh toán',
            'Điểm thưởng mua hàng',
            'Tích điểm từ đơn hàng'
        ];
        
        return $this->faker->randomElement($descriptions);
    }

    /**
     * Get Vietnamese redeem descriptions
     */
    private function getVietnameseRedeemDescription(): string
    {
        $descriptions = [
            'Đổi điểm lấy quà',
            'Sử dụng điểm giảm giá',
            'Đổi điểm thành tiền',
            'Quy đổi điểm thưởng',
            'Sử dụng điểm tích lũy',
            'Đổi điểm lấy voucher',
            'Thanh toán bằng điểm',
            'Đổi điểm lấy sản phẩm'
        ];
        
        return $this->faker->randomElement($descriptions);
    }

    /**
     * Get Vietnamese bonus descriptions
     */
    private function getVietnameseBonusDescription(): string
    {
        $descriptions = [
            'Điểm thưởng sinh nhật',
            'Điểm thưởng giới thiệu bạn',
            'Điểm thưởng khuyến mãi',
            'Điểm thưởng sự kiện',
            'Điểm thưởng đặc biệt',
            'Điểm thưởng chào mừng',
            'Điểm thưởng lễ tết',
            'Điểm thưởng thành viên mới'
        ];
        
        return $this->faker->randomElement($descriptions);
    }

    /**
     * Get Vietnamese adjustment descriptions
     */
    private function getVietnameseAdjustDescription(): string
    {
        $descriptions = [
            'Điều chỉnh điểm do lỗi hệ thống',
            'Bù trừ điểm bị mất',
            'Điều chỉnh số dư điểm',
            'Khôi phục điểm đã xóa',
            'Điều chỉnh điểm thủ công',
            'Bồi thường điểm thưởng',
            'Điều chỉnh do khiếu nại'
        ];
        
        return $this->faker->randomElement($descriptions);
    }
}