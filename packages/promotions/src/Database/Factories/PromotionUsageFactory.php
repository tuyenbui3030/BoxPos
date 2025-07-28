<?php

namespace Packages\Promotions\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Promotions\Models\PromotionUsage;
use Packages\Promotions\Models\Promotion;
use Packages\Customer\Models\Customer;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Promotions\Models\PromotionUsage>
 */
class PromotionUsageFactory extends Factory
{
    protected $model = PromotionUsage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $orderAmount = $this->faker->randomFloat(2, 100000, 5000000);
        $discountAmount = $this->faker->randomFloat(2, 10000, $orderAmount * 0.3);
        
        return [
            'store_id' => Store::factory(),
            'promotion_id' => \Packages\Promotions\Database\Factories\PromotionFactory::new(),
            'customer_id' => Customer::factory(),
            'usage_code' => $this->faker->unique()->regexify('PU[0-9]{8}'),
            'used_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'order_id' => $this->faker->optional()->numberBetween(1, 1000),
            'invoice_id' => $this->faker->optional()->numberBetween(1, 1000),
            'order_amount' => $orderAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => $orderAmount - $discountAmount,
            'discount_type' => $this->faker->randomElement(['percentage', 'fixed_amount', 'free_shipping', 'free_item']),
            'discount_value' => $this->faker->randomFloat(2, 5, 50),
            'items_affected' => $this->getAffectedItems(),
            'usage_channel' => $this->faker->randomElement(['in_store', 'online', 'mobile_app', 'phone_order']),
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'credit_card', 'e_wallet']),
            'notes' => $this->faker->optional()->sentence(),
            'is_valid' => true,
            'validation_errors' => [],
            'metadata' => $this->getUsageMetadata(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create usage for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create usage for specific promotion
     */
    public function forPromotion($promotion): static
    {
        return $this->state(fn (array $attributes) => [
            'promotion_id' => is_object($promotion) ? $promotion->id : $promotion,
        ]);
    }

    /**
     * Create usage for specific customer
     */
    public function forCustomer($customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => is_object($customer) ? $customer->id : $customer,
        ]);
    }

    /**
     * Create valid usage
     */
    public function valid(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_valid' => true,
            'validation_errors' => [],
        ]);
    }

    /**
     * Create invalid usage
     */
    public function invalid(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_valid' => false,
            'validation_errors' => $this->getValidationErrors(),
        ]);
    }

    /**
     * Create percentage discount usage
     */
    public function percentageDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'percentage',
            'discount_value' => $this->faker->randomFloat(2, 5, 30),
        ]);
    }

    /**
     * Create fixed amount discount usage
     */
    public function fixedDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'fixed_amount',
            'discount_value' => $this->faker->randomFloat(2, 10000, 500000),
        ]);
    }

    /**
     * Create free shipping usage
     */
    public function freeShipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'free_shipping',
            'discount_amount' => $this->faker->randomFloat(2, 20000, 100000),
        ]);
    }

    /**
     * Create in-store usage
     */
    public function inStore(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_channel' => 'in_store',
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'credit_card']),
        ]);
    }

    /**
     * Create online usage
     */
    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_channel' => 'online',
            'payment_method' => $this->faker->randomElement(['bank_transfer', 'credit_card', 'e_wallet']),
        ]);
    }

    /**
     * Create high value usage
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_amount' => $this->faker->randomFloat(2, 2000000, 10000000),
            'discount_amount' => $this->faker->randomFloat(2, 100000, 1000000),
        ]);
    }

    /**
     * Get affected items data
     */
    private function getAffectedItems(): array
    {
        $itemCount = $this->faker->numberBetween(1, 5);
        $items = [];
        
        for ($i = 0; $i < $itemCount; $i++) {
            $items[] = [
                'item_id' => $this->faker->numberBetween(1, 100),
                'item_name' => $this->getVietnameseItemName(),
                'quantity' => $this->faker->numberBetween(1, 10),
                'original_price' => $this->faker->randomFloat(2, 50000, 1000000),
                'discounted_price' => $this->faker->randomFloat(2, 40000, 800000),
                'discount_applied' => $this->faker->randomFloat(2, 5000, 200000)
            ];
        }
        
        return $items;
    }

    /**
     * Get usage metadata
     */
    private function getUsageMetadata(): array
    {
        return [
            'user_agent' => $this->faker->optional()->userAgent(),
            'ip_address' => $this->faker->optional()->ipv4(),
            'device_type' => $this->faker->optional()->randomElement(['desktop', 'mobile', 'tablet']),
            'browser' => $this->faker->optional()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            'referrer' => $this->faker->optional()->url(),
            'session_id' => $this->faker->optional()->uuid(),
            'applied_by' => $this->faker->randomElement(['customer', 'staff', 'system']),
            'application_method' => $this->faker->randomElement(['code_entry', 'automatic', 'staff_applied']),
            'promotion_source' => $this->faker->randomElement(['website', 'email', 'sms', 'social_media', 'in_store'])
        ];
    }

    /**
     * Get validation errors
     */
    private function getValidationErrors(): array
    {
        $errors = [
            'Khuyến mãi đã hết hạn',
            'Đã vượt quá số lần sử dụng cho phép',
            'Không đủ điều kiện áp dụng',
            'Sản phẩm không thuộc diện khuyến mãi',
            'Giá trị đơn hàng không đạt yêu cầu tối thiểu',
            'Khách hàng không thuộc nhóm được áp dụng',
            'Khuyến mãi không thể kết hợp với ưu đãi khác',
            'Mã khuyến mãi không hợp lệ'
        ];
        
        return $this->faker->randomElements($errors, $this->faker->numberBetween(1, 3));
    }

    /**
     * Get Vietnamese item names
     */
    private function getVietnameseItemName(): string
    {
        $items = [
            'Xi măng Portland',
            'Thép xây dựng',
            'Gạch ống',
            'Cát xây dựng',
            'Đá dăm',
            'Sơn nước',
            'Ống nước PVC',
            'Dây điện',
            'Ngói lợp',
            'Keo dán gạch',
            'Vữa khô',
            'Tôn lợp',
            'Cửa nhôm kính',
            'Gạch men',
            'Thiết bị vệ sinh'
        ];
        
        return $this->faker->randomElement($items);
    }
}