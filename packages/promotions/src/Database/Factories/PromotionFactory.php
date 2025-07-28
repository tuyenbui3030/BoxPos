<?php

namespace Packages\Promotions\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Promotions\Models\Promotion;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Promotions\Models\Promotion>
 */
class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-3 months', '+1 month');
        $endDate = $this->faker->dateTimeBetween($startDate, '+6 months');
        
        return [
            'store_id' => Store::factory(),
            'code' => $this->faker->unique()->regexify('PROMO[0-9]{6}'),
            'name' => $this->getVietnamesePromotionName(),
            'description' => $this->faker->paragraph(),
            'short_description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['percentage', 'fixed_amount', 'buy_x_get_y', 'free_shipping', 'bundle']),
            'target' => $this->faker->randomElement(['order', 'product', 'category', 'customer']),
            'status' => $this->faker->randomElement(['active', 'inactive', 'draft', 'expired']),
            'discount_value' => $this->faker->randomFloat(2, 5, 50),
            'min_order_amount' => $this->faker->optional()->randomFloat(2, 100000, 2000000),
            'max_order_amount' => $this->faker->optional()->randomFloat(2, 5000000, 20000000),
            'max_discount_amount' => $this->faker->optional()->randomFloat(2, 50000, 500000),
            'usage_limit' => $this->faker->optional()->numberBetween(10, 1000),
            'usage_limit_per_customer' => $this->faker->optional()->numberBetween(1, 5),
            'current_usage' => 0,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'buy_quantity' => $this->faker->optional()->numberBetween(2, 10),
            'get_quantity' => $this->faker->optional()->numberBetween(1, 3),
            'get_discount_percent' => $this->faker->optional()->randomFloat(2, 10, 100),
            'get_free' => $this->faker->boolean(30),
            'time_restrictions' => [],
            'day_restrictions' => [],
            'customer_groups' => [],
            'customer_ids' => [],
            'new_customers_only' => $this->faker->boolean(20),
            'existing_customers_only' => $this->faker->boolean(10),
            'included_products' => [],
            'excluded_products' => [],
            'included_categories' => [],
            'excluded_categories' => [],
            'sales_channels' => ['online', 'in_store'],
            'payment_methods' => ['cash', 'card', 'bank_transfer'],
            'combinable' => $this->faker->boolean(30),
            'combinable_with' => [],
            'priority' => $this->faker->numberBetween(1, 10),
            'display_name' => $this->getVietnamesePromotionName(),
            'terms_conditions' => $this->getVietnameseTermsAndConditions(),
            'banner_image' => null,
            'show_on_website' => $this->faker->boolean(80),
            'show_in_app' => $this->faker->boolean(70),
            'total_discount_given' => 0,
            'total_orders_affected' => 0,
            'total_revenue_impact' => 0,
            'metadata' => [],
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create promotion for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create active promotion
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+3 months'),
        ]);
    }

    /**
     * Create featured promotion
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_on_website' => true,
            'show_in_app' => true,
        ]);
    }

    /**
     * Create percentage discount promotion
     */
    public function percentageDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'percentage',
            'discount_value' => $this->faker->randomFloat(2, 5, 30),
        ]);
    }

    /**
     * Create fixed amount discount promotion
     */
    public function fixedDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fixed_amount',
            'discount_value' => $this->faker->randomFloat(2, 10000, 500000),
        ]);
    }

    /**
     * Create buy X get Y promotion
     */
    public function buyXGetY(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'buy_x_get_y',
            'buy_quantity' => $this->faker->numberBetween(2, 5),
            'get_quantity' => $this->faker->numberBetween(1, 2),
            'get_free' => true,
        ]);
    }

    /**
     * Create free shipping promotion
     */
    public function freeShipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'free_shipping',
            'min_order_amount' => $this->faker->randomFloat(2, 500000, 2000000),
        ]);
    }

    /**
     * Create bundle promotion
     */
    public function bundle(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'bundle',
            'discount_value' => $this->faker->randomFloat(2, 10, 25),
        ]);
    }

    /**
     * Create expired promotion
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $this->faker->dateTimeBetween('-6 months', '-3 months'),
            'end_date' => $this->faker->dateTimeBetween('-3 months', '-1 month'),
            'status' => 'expired',
        ]);
    }

    /**
     * Create upcoming promotion
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+1 month', '+3 months'),
        ]);
    }

    /**
     * Get Vietnamese promotion names
     */
    private function getVietnamesePromotionName(): string
    {
        $names = [
            'Khuyến mãi mùa hè',
            'Giảm giá cuối năm',
            'Ưu đãi khách hàng thân thiết',
            'Khuyến mãi khai trương',
            'Giảm giá sốc',
            'Mua 2 tặng 1',
            'Freeship toàn quốc',
            'Combo tiết kiệm',
            'Flash sale 24h',
            'Khuyến mãi sinh nhật',
            'Ưu đãi tết nguyên đán',
            'Giảm giá Black Friday',
            'Khuyến mãi 8/3',
            'Ưu đãi 30/4 - 1/5',
            'Giảm giá back to school',
            'Khuyến mãi trung thu',
            'Ưu đãi noel',
            'Sale cuối tuần',
            'Khuyến mãi thứ 6 đen tối',
            'Ưu đãi mua sắm online'
        ];
        
        return $this->faker->randomElement($names);
    }

    /**
     * Get Vietnamese promotion conditions
     */
    private function getVietnamesePromotionConditions(): array
    {
        $conditions = [
            'Áp dụng cho đơn hàng từ 500.000đ',
            'Không áp dụng cùng khuyến mãi khác',
            'Chỉ áp dụng cho khách hàng mới',
            'Áp dụng cho thành viên VIP',
            'Giới hạn 1 lần/khách hàng',
            'Áp dụng cho thanh toán online',
            'Không áp dụng cho hàng sale',
            'Áp dụng trong giờ hành chính',
            'Chỉ áp dụng tại cửa hàng',
            'Áp dụng cho đơn hàng trên 1.000.000đ'
        ];
        
        return $this->faker->randomElements($conditions, $this->faker->numberBetween(1, 4));
    }

    /**
     * Get Vietnamese terms and conditions
     */
    private function getVietnameseTermsAndConditions(): array
    {
        return [
            'Khuyến mãi có thể kết thúc sớm khi hết ngân sách',
            'Không áp dụng cho hàng đã giảm giá',
            'Khuyến mãi không quy đổi thành tiền mặt',
            'Cửa hàng có quyền từ chối áp dụng khuyến mãi nếu phát hiện gian lận',
            'Khuyến mãi chỉ áp dụng cho sản phẩm còn hàng',
            'Không áp dụng cùng với các chương trình khuyến mãi khác',
            'Quyết định của cửa hàng là quyết định cuối cùng',
            'Khuyến mãi có thể thay đổi mà không cần báo trước'
        ];
    }

    /**
     * Get Vietnamese promotion tags
     */
    private function getVietnamesePromotionTags(): array
    {
        $tags = [
            'giảm giá',
            'khuyến mãi',
            'ưu đãi',
            'sale',
            'freeship',
            'combo',
            'flash sale',
            'hot deal',
            'limited time',
            'best seller',
            'new arrival',
            'clearance',
            'seasonal',
            'holiday',
            'weekend'
        ];
        
        return $this->faker->randomElements($tags, $this->faker->numberBetween(2, 5));
    }
}