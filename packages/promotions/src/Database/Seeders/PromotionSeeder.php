<?php

namespace Packages\Promotions\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Packages\Promotions\Models\Promotion;
use Packages\Promotions\Models\PromotionUsage;
use Packages\Store\Models\Store;
use Packages\Customer\Models\Customer;
use Packages\User\Models\User;
use Carbon\Carbon;

class PromotionSeeder extends BasePackageSeeder
{
    public function run(): void
    {
        $this->ensureSeedingAllowed();
        
        $this->executeWithTransaction(function () {
            $this->seedPromotionData();
        });
    }

    /**
     * Seed promotions and promotion usages
     */
    private function seedPromotionData(): void
    {
        $this->logSeedingProgress('promotion_seeding_started');

        $this->seedForAllStores(function (Store $store) {
            // Create various types of promotions for each store
            $promotions = $this->createPromotionsForStore($store);
            
            // Create promotion usages history
            foreach ($promotions as $promotion) {
                $this->createPromotionUsages($promotion);
            }
        });

        $this->logSeedingProgress('promotion_seeding_completed');
    }

    /**
     * Create different types of promotions for a store
     */
    private function createPromotionsForStore(Store $store): array
    {
        $this->logSeedingProgress('creating_promotions_for_store', [
            'store_id' => $store->id,
            'store_name' => $store->name
        ]);

        $createdBy = $store->users()->first();
        $promotions = [];

        // Create percentage discount promotions
        $promotions[] = $this->createPercentagePromotion($store, $createdBy);
        $promotions[] = $this->createFixedAmountPromotion($store, $createdBy);
        
        // Create seasonal promotions
        $promotions[] = $this->createSeasonalPromotion($store, $createdBy);
        
        // Create customer-specific promotions
        $promotions[] = $this->createVipCustomerPromotion($store, $createdBy);

        // In development, create more promotions
        if ($this->isDevelopment) {
            $promotions[] = $this->createNewCustomerPromotion($store, $createdBy);
            $promotions[] = $this->createFlashSalePromotion($store, $createdBy);
        }

        $this->logSeedingProgress('promotions_created_for_store', [
            'store_id' => $store->id,
            'promotion_count' => count($promotions)
        ]);

        return $promotions;
    }

    /**
     * Create percentage discount promotion
     */
    private function createPercentagePromotion(Store $store, ?User $createdBy): Promotion
    {
        return Promotion::create([
            'store_id' => $store->id,
            'code' => 'DISCOUNT' . rand(10, 99),
            'name' => 'Giảm giá ' . rand(10, 30) . '% toàn bộ đơn hàng',
            'type' => 'percentage',
            'target' => 'order',
            'status' => 'active',
            'description' => 'Áp dụng giảm giá phần trăm cho toàn bộ đơn hàng khi đạt giá trị tối thiểu',
            'start_date' => Carbon::now()->subDays(rand(30, 90)),
            'end_date' => Carbon::now()->addDays(rand(30, 90)),
            'usage_limit' => rand(100, 500),
            'current_usage' => 0,
            'min_order_amount' => rand(500000, 2000000), // 500k-2M VND
            'max_discount_amount' => rand(200000, 500000), // 200k-500k VND
            'discount_value' => rand(10, 30),
            'terms_conditions' => 'Áp dụng cho tất cả sản phẩm. Không áp dụng cùng với khuyến mãi khác.',
            'priority' => rand(1, 10),
            'combinable' => false,
            'show_on_website' => true,
            'show_in_app' => true,
            'created_by' => $createdBy?->id,
            'metadata' => [
                'created_via' => 'seeder',
                'promotion_category' => 'general_discount',
                'target_audience' => 'all_customers',
            ],
        ]);
    }

    /**
     * Create fixed amount discount promotion
     */
    private function createFixedAmountPromotion(Store $store, ?User $createdBy): Promotion
    {
        $discountAmount = rand(50000, 300000); // 50k-300k VND
        
        return Promotion::create([
            'store_id' => $store->id,
            'code' => 'FIXED' . rand(100, 999),
            'name' => 'Giảm ' . number_format($discountAmount) . 'đ cho đơn hàng',
            'type' => 'fixed_amount',
            'target' => 'order',
            'status' => 'active',
            'description' => 'Giảm giá cố định cho đơn hàng đạt giá trị tối thiểu',
            'start_date' => Carbon::now()->subDays(rand(15, 60)),
            'end_date' => Carbon::now()->addDays(rand(15, 60)),
            'usage_limit' => rand(50, 200),
            'current_usage' => 0,
            'min_order_amount' => $discountAmount * 3, // Minimum 3x discount amount
            'max_discount_amount' => $discountAmount,
            'discount_value' => $discountAmount,
            'terms_conditions' => 'Áp dụng cho đơn hàng đạt giá trị tối thiểu. Không áp dụng cùng khuyến mãi khác.',
            'priority' => rand(1, 10),
            'combinable' => false,
            'show_on_website' => true,
            'show_in_app' => true,
            'created_by' => $createdBy?->id,
            'metadata' => [
                'created_via' => 'seeder',
                'promotion_category' => 'fixed_discount',
                'target_audience' => 'all_customers',
            ],
        ]);
    }

    /**
     * Create seasonal promotion
     */
    private function createSeasonalPromotion(Store $store, ?User $createdBy): Promotion
    {
        $seasons = [
            'Tết Nguyên Đán' => ['start' => '-30 days', 'end' => '+15 days'],
            'Khuyến mãi hè' => ['start' => '-45 days', 'end' => '+30 days'],
            'Black Friday' => ['start' => '-7 days', 'end' => '+3 days'],
            'Giáng sinh' => ['start' => '-20 days', 'end' => '+5 days'],
        ];
        
        $seasonName = array_rand($seasons);
        $seasonData = $seasons[$seasonName];
        
        return Promotion::create([
            'store_id' => $store->id,
            'code' => 'SEASON' . rand(100, 999),
            'name' => 'Khuyến mãi ' . $seasonName,
            'type' => 'percentage',
            'target' => 'order',
            'status' => 'active',
            'description' => 'Chương trình khuyến mãi đặc biệt nhân dịp ' . $seasonName,
            'start_date' => Carbon::now()->modify($seasonData['start']),
            'end_date' => Carbon::now()->modify($seasonData['end']),
            'usage_limit' => rand(200, 1000),
            'current_usage' => 0,
            'min_order_amount' => rand(300000, 1000000), // 300k-1M VND
            'max_discount_amount' => rand(300000, 800000), // 300k-800k VND
            'discount_value' => rand(15, 40),
            'terms_conditions' => 'Chương trình có thời hạn. Áp dụng cho tất cả sản phẩm trong kho.',
            'priority' => rand(5, 15),
            'combinable' => true,
            'show_on_website' => true,
            'show_in_app' => true,
            'created_by' => $createdBy?->id,
            'metadata' => [
                'created_via' => 'seeder',
                'promotion_category' => 'seasonal',
                'season' => $seasonName,
                'target_audience' => 'all_customers',
            ],
        ]);
    }

    /**
     * Create VIP customer promotion
     */
    private function createVipCustomerPromotion(Store $store, ?User $createdBy): Promotion
    {
        // Get some VIP customers for this store
        $vipCustomers = Customer::where('store_id', $store->id)
            ->where('customer_group', 'vip')
            ->limit(10)
            ->pluck('id')
            ->toArray();

        return Promotion::create([
            'store_id' => $store->id,
            'code' => 'VIP' . rand(100, 999),
            'name' => 'Ưu đãi đặc biệt khách hàng VIP',
            'type' => 'percentage',
            'target' => 'customer',
            'status' => 'active',
            'description' => 'Chương trình ưu đãi dành riêng cho khách hàng VIP',
            'start_date' => Carbon::now()->subDays(rand(60, 120)),
            'end_date' => Carbon::now()->addDays(rand(60, 120)),
            'usage_limit' => count($vipCustomers) * 5, // Each VIP can use 5 times
            'usage_limit_per_customer' => 5,
            'current_usage' => 0,
            'min_order_amount' => rand(1000000, 3000000), // 1M-3M VND
            'max_discount_amount' => rand(500000, 1000000), // 500k-1M VND
            'discount_value' => rand(20, 35),
            'customer_ids' => $vipCustomers,
            'terms_conditions' => 'Chỉ áp dụng cho khách hàng VIP. Mỗi khách hàng được sử dụng tối đa 5 lần.',
            'priority' => rand(15, 20),
            'combinable' => false,
            'show_on_website' => true,
            'show_in_app' => true,
            'created_by' => $createdBy?->id,
            'metadata' => [
                'created_via' => 'seeder',
                'promotion_category' => 'vip_exclusive',
                'target_audience' => 'vip_customers',
                'eligible_customer_count' => count($vipCustomers),
            ],
        ]);
    }

    /**
     * Create new customer promotion
     */
    private function createNewCustomerPromotion(Store $store, ?User $createdBy): Promotion
    {
        return Promotion::create([
            'store_id' => $store->id,
            'code' => 'WELCOME' . rand(10, 99),
            'name' => 'Ưu đãi khách hàng mới',
            'type' => 'fixed_amount',
            'target' => 'customer',
            'status' => 'active',
            'description' => 'Chào mừng khách hàng mới với ưu đãi đặc biệt',
            'start_date' => Carbon::now()->subDays(rand(30, 60)),
            'end_date' => Carbon::now()->addDays(rand(90, 180)),
            'usage_limit' => rand(100, 300),
            'current_usage' => 0,
            'min_order_amount' => rand(200000, 500000), // 200k-500k VND
            'max_discount_amount' => rand(100000, 200000), // 100k-200k VND
            'discount_value' => rand(50000, 150000),
            'new_customers_only' => true,
            'terms_conditions' => 'Chỉ áp dụng cho khách hàng đăng ký trong vòng 30 ngày.',
            'priority' => rand(5, 10),
            'combinable' => false,
            'show_on_website' => true,
            'show_in_app' => true,
            'created_by' => $createdBy?->id,
            'metadata' => [
                'created_via' => 'seeder',
                'promotion_category' => 'new_customer',
                'target_audience' => 'new_customers',
            ],
        ]);
    }

    /**
     * Create flash sale promotion
     */
    private function createFlashSalePromotion(Store $store, ?User $createdBy): Promotion
    {
        return Promotion::create([
            'store_id' => $store->id,
            'code' => 'FLASH' . rand(100, 999),
            'name' => 'Flash Sale - Giảm giá sốc',
            'type' => 'percentage',
            'target' => 'order',
            'status' => 'active',
            'description' => 'Giảm giá sốc trong thời gian có hạn',
            'start_date' => Carbon::now()->addHours(rand(1, 24)),
            'end_date' => Carbon::now()->addHours(rand(25, 72)),
            'usage_limit' => rand(20, 50),
            'current_usage' => 0,
            'min_order_amount' => rand(300000, 800000), // 300k-800k VND
            'max_discount_amount' => rand(400000, 600000), // 400k-600k VND
            'discount_value' => rand(30, 50),
            'terms_conditions' => 'Số lượng có hạn. Áp dụng theo thứ tự đặt hàng.',
            'priority' => rand(20, 25),
            'combinable' => false,
            'show_on_website' => true,
            'show_in_app' => true,
            'created_by' => $createdBy?->id,
            'metadata' => [
                'created_via' => 'seeder',
                'promotion_category' => 'flash_sale',
                'target_audience' => 'all_customers',
                'urgency_level' => 'high',
            ],
        ]);
    }

    /**
     * Create promotion usages history
     */
    private function createPromotionUsages(Promotion $promotion): void
    {
        $this->logSeedingProgress('creating_promotion_usages', [
            'promotion_id' => $promotion->id,
            'promotion_code' => $promotion->code
        ]);

        // Get customers for this store
        $customers = Customer::where('store_id', $promotion->store_id)
            ->limit($this->getRecordCount(20, 5))
            ->get();

        if ($customers->isEmpty()) {
            return;
        }

        // Create usage history based on promotion type and rules
        $usageCount = $this->calculateUsageCount($promotion);
        
        for ($i = 0; $i < $usageCount; $i++) {
            $customer = $customers->random();
            
            // Check if customer is eligible (simplified check)
            if ($promotion->customer_ids && !in_array($customer->id, $promotion->customer_ids)) {
                continue;
            }

            $this->createPromotionUsage($promotion, $customer);
        }

        // Update promotion usage count
        $actualUsageCount = $promotion->usages()->count();
        $promotion->update(['current_usage' => $actualUsageCount]);

        $this->logSeedingProgress('promotion_usages_created', [
            'promotion_id' => $promotion->id,
            'usage_count' => $actualUsageCount
        ]);
    }

    /**
     * Calculate how many usages to create for a promotion
     */
    private function calculateUsageCount(Promotion $promotion): int
    {
        $baseCount = $this->getRecordCount(15, 3);
        
        // Adjust based on promotion type
        $multiplier = match($promotion->type) {
            'percentage' => 1.2,   // Popular promotions
            'fixed_amount' => 1.0, // Standard usage
            default => 1.0,
        };
        
        $calculatedCount = intval($baseCount * $multiplier);
        
        // Don't exceed usage limit
        if ($promotion->usage_limit > 0) {
            $calculatedCount = min($calculatedCount, $promotion->usage_limit);
        }
        
        return max(1, $calculatedCount);
    }

    /**
     * Create individual promotion usage record
     */
    private function createPromotionUsage(Promotion $promotion, Customer $customer): void
    {
        $usageDate = $this->getRandomUsageDate($promotion);
        $orderAmount = rand(
            max($promotion->min_order_amount, 100000), 
            $promotion->min_order_amount * 5
        );
        
        // Calculate discount based on promotion type
        $discountAmount = $this->calculateDiscountAmount($promotion, $orderAmount);

        PromotionUsage::create([
            'store_id' => $promotion->store_id,
            'promotion_id' => $promotion->id,
            'customer_id' => $customer->id,
            'order_type' => 'sales_order',
            'order_id' => rand(1, 10000),
            'order_number' => 'SO' . str_pad(rand(1, 99999), 8, '0', STR_PAD_LEFT),
            'used_at' => $usageDate,
            'order_subtotal' => $orderAmount,
            'discount_amount' => $discountAmount,
            'order_total' => $orderAmount - $discountAmount,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'sales_channel' => $this->getRandomChannel(),
            'is_valid' => true,
            'metadata' => [
                'created_via' => 'seeder',
                'promotion_type' => $promotion->type,
                'channel' => $this->getRandomChannel(),
                'usage_context' => $this->getUsageContext($promotion),
            ],
        ]);
    }

    /**
     * Calculate discount amount based on promotion
     */
    private function calculateDiscountAmount(Promotion $promotion, float $orderAmount): float
    {
        if ($promotion->type === 'percentage') {
            $discount = $orderAmount * ($promotion->discount_value / 100);
            if ($promotion->max_discount_amount > 0) {
                $discount = min($discount, $promotion->max_discount_amount);
            }
            return $discount;
        } elseif ($promotion->type === 'fixed_amount') {
            return min($promotion->discount_value, $orderAmount);
        }
        
        return 0;
    }

    /**
     * Get random usage date within promotion period
     */
    private function getRandomUsageDate(Promotion $promotion): Carbon
    {
        $startDate = max($promotion->start_date, Carbon::now()->subDays(180));
        $endDate = min($promotion->end_date, Carbon::now());
        
        $daysDiff = $startDate->diffInDays($endDate);
        if ($daysDiff <= 0) {
            return $startDate;
        }
        
        return $startDate->copy()->addDays(rand(0, $daysDiff));
    }

    /**
     * Get usage context based on promotion type
     */
    private function getUsageContext(Promotion $promotion): string
    {
        return match($promotion->type) {
            'percentage' => 'percentage_discount',
            'fixed_amount' => 'fixed_discount',
            default => 'general_promotion',
        };
    }

    /**
     * Get random sales channel
     */
    private function getRandomChannel(): string
    {
        $channels = ['pos', 'online', 'mobile_app', 'phone_order', 'in_store'];
        return $channels[array_rand($channels)];
    }
}