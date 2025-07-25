<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\Promotions\Models\Promotion;
use Packages\Promotions\Models\PromotionUsage;
use Packages\Customer\Models\Customer;
use Packages\User\Models\User;
use Carbon\Carbon;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎁 Seeding Promotions...');

        $stores = Store::all();

        foreach ($stores as $store) {
            $this->createPromotionsForStore($store);
        }

        $this->command->info('✅ Promotions seeded successfully!');
    }

    private function createPromotionsForStore(Store $store): void
    {
        $createdBy = User::where('email', 'admin@' . strtolower($store->slug) . '.boxpos.vn')->first();

        // Create 5-8 promotions per store
        $promotionCount = rand(5, 8);

        for ($i = 1; $i <= $promotionCount; $i++) {
            $promotion = $this->createPromotion($store, $createdBy, $i);
            $this->createPromotionUsages($promotion);
        }
    }

    private function createPromotion(Store $store, ?User $createdBy, int $sequence): Promotion
    {
        $promotionType = $this->getRandomPromotionType();
        $startDate = Carbon::now()->subDays(rand(30, 90));
        $endDate = $startDate->copy()->addDays(rand(30, 60));
        
        return Promotion::create([
            'store_id' => $store->id,
            'code' => $this->generatePromotionCode($store, $sequence),
            'name' => $this->getPromotionName($promotionType),
            'description' => $this->getPromotionDescription($promotionType),
            'type' => $promotionType,
            'target' => $this->getPromotionTarget($promotionType),
            'status' => $this->getPromotionStatus($startDate, $endDate),
            'discount_value' => $this->getDiscountValue($promotionType),
            'max_discount_amount' => $this->getMaximumDiscountAmount($promotionType),
            'min_order_amount' => $this->getMinimumOrderAmount($promotionType),
            'buy_quantity' => $promotionType === 'buy_x_get_y' ? rand(2, 5) : null,
            'get_quantity' => $promotionType === 'buy_x_get_y' ? 1 : null,
            'get_discount_percent' => $promotionType === 'buy_x_get_y' ? 100 : 0,
            'get_free' => $promotionType === 'buy_x_get_y',
            'usage_limit' => $this->getUsageLimit($promotionType),
            'usage_limit_per_customer' => $this->getUsageLimitPerCustomer($promotionType),
            'current_usage' => 0,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'customer_groups' => json_encode($this->getApplicableCustomerTypes()),
            'included_categories' => json_encode($this->getApplicableProductCategories()),
            'combinable' => rand(0, 1) === 1,
            'priority' => rand(1, 10),
            'display_name' => $this->getPromotionName($promotionType),
            'terms_conditions' => $this->getTermsAndConditions($promotionType),
            'show_on_website' => true,
            'show_in_app' => true,
            'created_by' => $createdBy?->id,
            'metadata' => json_encode([
                'created_via' => 'seeder',
                'promotion_channel' => $this->getPromotionChannel(),
            ]),
        ]);
    }

    private function createPromotionUsages(Promotion $promotion): void
    {
        $customers = Customer::where('store_id', $promotion->store_id)->get();
        
        if ($customers->isEmpty()) {
            return;
        }

        // Create 5-15 usages per promotion
        $usageCount = rand(5, 15);
        $usageCount = min($usageCount, $promotion->usage_limit ?? $usageCount);

        for ($i = 0; $i < $usageCount; $i++) {
            $customer = $customers->random();
            $this->createPromotionUsage($promotion, $customer);
        }

        // Update promotion usage count
        $promotion->update([
            'current_usage' => $promotion->usages()->count(),
        ]);
    }

    private function createPromotionUsage(Promotion $promotion, Customer $customer): void
    {
        $usageDate = Carbon::parse($promotion->start_date)->addDays(rand(0, 30));
        
        $orderAmount = $this->getRandomOrderAmount();
        $discountAmount = $this->calculateDiscountAmount($promotion);

        PromotionUsage::create([
            'store_id' => $promotion->store_id,
            'promotion_id' => $promotion->id,
            'customer_id' => $customer->id,
            'order_number' => 'SO' . str_pad(rand(1, 99999), 8, '0', STR_PAD_LEFT),
            'order_id' => rand(1, 100), // Fake order ID
            'order_type' => 'sales_order',
            'used_at' => $usageDate,
            'order_subtotal' => $orderAmount,
            'discount_amount' => $discountAmount,
            'order_total' => $orderAmount - $discountAmount,
            'items_count' => rand(1, 5),
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
        ]);
    }

    private function generatePromotionCode(Store $store, int $sequence): string
    {
        $storeCode = strtoupper(substr($store->slug, 0, 3));
        $sequenceCode = str_pad($sequence, 2, '0', STR_PAD_LEFT);
        $randomCode = strtoupper(substr(md5(rand()), 0, 4));
        
        return "{$storeCode}PROMO{$sequenceCode}{$randomCode}";
    }

    private function getRandomPromotionType(): string
    {
        $types = ['percentage', 'fixed_amount', 'buy_x_get_y', 'bundle', 'shipping', 'loyalty_points'];
        return $types[array_rand($types)];
    }

    private function getPromotionName(string $type): string
    {
        $names = [
            'percentage' => 'Giảm giá phần trăm',
            'fixed_amount' => 'Giảm giá cố định',
            'buy_x_get_y' => 'Mua X tặng Y',
            'bundle' => 'Combo sản phẩm',
            'shipping' => 'Miễn phí vận chuyển',
            'loyalty_points' => 'Tích điểm thưởng',
        ];
        
        return $names[$type] ?? 'Khuyến mãi đặc biệt';
    }

    private function getPromotionDescription(string $type): string
    {
        $descriptions = [
            'percentage' => 'Giảm giá theo phần trăm cho đơn hàng',
            'fixed_amount' => 'Giảm giá số tiền cố định',
            'buy_x_get_y' => 'Mua sản phẩm được tặng kèm',
            'bundle' => 'Giảm giá khi mua combo sản phẩm',
            'shipping' => 'Miễn phí phí vận chuyển',
            'loyalty_points' => 'Tích điểm thưởng cho khách hàng',
        ];
        
        return $descriptions[$type] ?? 'Chương trình khuyến mãi hấp dẫn';
    }

    private function getDiscountType(string $promotionType): string
    {
        return match($promotionType) {
            'percentage' => 'percentage',
            'fixed_amount' => 'fixed_amount',
            'buy_x_get_y' => 'buy_x_get_y',
            'bundle' => 'bundle',
            'shipping' => 'shipping',
            'loyalty_points' => 'loyalty_points',
            default => 'percentage',
        };
    }

    private function getDiscountValue(string $promotionType): float
    {
        return match($promotionType) {
            'percentage' => rand(5, 30), // 5-30%
            'fixed_amount' => rand(50000, 500000), // 50k-500k VND
            'buy_x_get_y' => 1, // Buy 1 get 1
            'bundle' => rand(10, 25), // 10-25% discount for bundle
            'shipping' => 0, // No value for free shipping
            'loyalty_points' => rand(100, 1000), // Points to award
            default => rand(10, 20),
        };
    }

    private function getMinimumOrderAmount(string $promotionType): float
    {
        return match($promotionType) {
            'percentage' => rand(500000, 2000000), // 500k-2M VND
            'fixed_amount' => rand(1000000, 3000000), // 1M-3M VND
            'shipping' => rand(300000, 1000000), // 300k-1M VND
            'buy_x_get_y' => rand(200000, 800000), // 200k-800k VND
            'bundle' => rand(800000, 2000000), // 800k-2M VND
            'loyalty_points' => 0, // No minimum for loyalty points
            default => 0,
        };
    }

    private function getMaximumDiscountAmount(string $promotionType): ?float
    {
        return match($promotionType) {
            'percentage' => rand(200000, 1000000), // 200k-1M VND max
            default => null,
        };
    }

    private function getUsageLimit(string $promotionType): ?int
    {
        return rand(50, 200); // 50-200 uses
    }

    private function getUsageLimitPerCustomer(string $promotionType): ?int
    {
        return rand(1, 3); // 1-3 uses per customer
    }

    private function getPromotionStatus(Carbon $startDate, Carbon $endDate): string
    {
        $now = Carbon::now();

        if ($endDate->lt($now)) {
            return 'expired';
        }

        if ($startDate->gt($now)) {
            return 'draft';
        }

        // Random status for active promotions
        $statuses = ['active', 'paused'];
        return $statuses[array_rand($statuses)];
    }

    private function getApplicableCustomerTypes(): array
    {
        $types = ['retail', 'wholesale', 'vip'];
        return array_slice($types, 0, rand(1, 3));
    }

    private function getApplicableProductCategories(): array
    {
        return ['all']; // Apply to all categories for simplicity
    }

    private function getTermsAndConditions(string $promotionType): string
    {
        return "Áp dụng cho khuyến mãi {$promotionType}. Không áp dụng đồng thời với các chương trình khác.";
    }

    private function getPromotionChannel(): string
    {
        $channels = ['online', 'offline', 'mobile_app', 'social_media'];
        return $channels[array_rand($channels)];
    }

    private function calculateDiscountAmount(Promotion $promotion): float
    {
        $orderAmount = $this->getRandomOrderAmount();
        
        return match($promotion->discount_type) {
            'percentage' => min($orderAmount * ($promotion->discount_value / 100), 
                              $promotion->maximum_discount_amount ?? $orderAmount),
            'fixed_amount' => $promotion->discount_value,
            default => rand(50000, 200000),
        };
    }

    private function getRandomOrderAmount(): float
    {
        return rand(500000, 5000000); // 500k-5M VND
    }

    private function getPromotionTarget(string $promotionType): string
    {
        return match($promotionType) {
            'percentage', 'fixed_amount' => 'order',
            'buy_x_get_y' => 'product',
            'bundle' => 'category',
            'shipping' => 'shipping',
            'loyalty_points' => 'customer',
            default => 'order',
        };
    }
}
