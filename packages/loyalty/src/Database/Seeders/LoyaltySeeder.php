<?php

namespace Packages\Loyalty\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Packages\Loyalty\Models\LoyaltyProgram;
use Packages\Loyalty\Models\LoyaltyMembership;
use Packages\Loyalty\Models\LoyaltyTransaction;
use Packages\Store\Models\Store;
use Packages\Customer\Models\Customer;
use Packages\User\Models\User;
use Carbon\Carbon;

class LoyaltySeeder extends BasePackageSeeder
{
    public function run(): void
    {
        $this->ensureSeedingAllowed();
        
        $this->executeWithTransaction(function () {
            $this->seedLoyaltyData();
        });
    }

    /**
     * Seed loyalty programs, memberships and transactions
     */
    private function seedLoyaltyData(): void
    {
        $this->logSeedingProgress('loyalty_seeding_started');

        $this->seedForAllStores(function (Store $store) {
            // Create loyalty program for each store
            $loyaltyProgram = $this->createLoyaltyProgram($store);
            
            // Create memberships for customers
            $customers = Customer::where('store_id', $store->id)
                ->limit($this->getRecordCount(20, 5))
                ->get();
                
            foreach ($customers as $customer) {
                $membership = $this->createLoyaltyMembership($loyaltyProgram, $customer);
                
                // Create transactions for each membership
                $transactionCount = $this->getRecordCount(10, 3);
                $this->createLoyaltyTransactions($membership, rand(3, $transactionCount));
            }
        });

        $this->logSeedingProgress('loyalty_seeding_completed');
    }

    /**
     * Create loyalty program with membership tiers for a store
     */
    private function createLoyaltyProgram(Store $store): LoyaltyProgram
    {
        $this->logSeedingProgress('creating_loyalty_program', [
            'store_id' => $store->id,
            'store_name' => $store->name
        ]);

        $createdBy = $store->users()->first();

        $loyaltyProgram = LoyaltyProgram::create([
            'store_id' => $store->id,
            'name' => 'Chương trình khách hàng thân thiết ' . $store->name,
            'code' => 'LOYALTY_' . strtoupper($store->slug ?? 'STORE' . $store->id),
            'description' => 'Tích điểm mỗi khi mua hàng và đổi quà hấp dẫn. Hệ thống phân hạng thành viên với nhiều ưu đãi.',
            'type' => 'points',
            'status' => 'active',
            'auto_enrollment' => true,
            'start_date' => Carbon::now()->subMonths(6)->toDateString(),
            'end_date' => Carbon::now()->addYears(2)->toDateString(),
            'earn_rate' => 1, // 1 point per 1 VND
            'redeem_rate' => 1000, // 1000 VND per point
            'min_points_to_redeem' => 100,
            'max_points_per_transaction' => 10000,
            'points_expiry_days' => 365, // 1 year
            'tier_config' => [
                ['tier_name' => 'bronze', 'points_required' => 0, 'multiplier' => 1.0],
                ['tier_name' => 'silver', 'points_required' => 1000, 'multiplier' => 1.2],
                ['tier_name' => 'gold', 'points_required' => 5000, 'multiplier' => 1.5],
                ['tier_name' => 'platinum', 'points_required' => 10000, 'multiplier' => 2.0],
            ],
            'tier_based_earning' => true,
            'tier_benefits' => [
                'bronze' => ['discount_percent' => 0, 'free_shipping' => false, 'priority_support' => false],
                'silver' => ['discount_percent' => 5, 'free_shipping' => false, 'priority_support' => true],
                'gold' => ['discount_percent' => 10, 'free_shipping' => true, 'priority_support' => true],
                'platinum' => ['discount_percent' => 15, 'free_shipping' => true, 'priority_support' => true, 'exclusive_offers' => true],
            ],
            'min_purchase_amount' => 50000, // 50k VND minimum
            'welcome_bonus' => true,
            'welcome_bonus_points' => 100,
            'referral_bonus' => true,
            'referral_bonus_points' => 200,
            'birthday_bonus' => true,
            'birthday_bonus_points' => 500,
            'terms_conditions' => 'Điều khoản và điều kiện chương trình khách hàng thân thiết. Điểm tích lũy có thời hạn 1 năm. Thành viên có thể đổi điểm lấy quà hoặc giảm giá.',
            'created_by' => $createdBy?->id,
            'metadata' => [
                'created_via' => 'seeder',
                'program_version' => '1.0',
                'auto_enrollment' => true,
                'welcome_bonus_enabled' => true,
                'referral_program_enabled' => true,
                'birthday_bonus_enabled' => true,
            ],
        ]);

        $this->logSeedingProgress('loyalty_program_created', [
            'program_id' => $loyaltyProgram->id,
            'program_code' => $loyaltyProgram->code,
            'store_id' => $store->id
        ]);

        return $loyaltyProgram;
    }

    /**
     * Create loyalty membership for customer with proper tier calculation
     */
    private function createLoyaltyMembership(LoyaltyProgram $loyaltyProgram, Customer $customer): LoyaltyMembership
    {
        $joinDate = Carbon::now()->subDays(rand(30, 365));
        $lifetimePoints = rand(100, 15000);
        $currentPoints = intval($lifetimePoints * rand(60, 90) / 100); // 60-90% available
        $pointsRedeemed = $lifetimePoints - $currentPoints;
        
        // Calculate tier based on lifetime points
        $tier = $this->calculateTier($loyaltyProgram, $lifetimePoints);

        $membership = LoyaltyMembership::create([
            'store_id' => $loyaltyProgram->store_id,
            'program_id' => $loyaltyProgram->id,
            'customer_id' => $customer->id,
            'membership_number' => $this->generateMembershipNumber($loyaltyProgram, $customer),
            'status' => 'active',
            'enrolled_date' => $joinDate->toDateString(),
            'last_activity_date' => Carbon::now()->subDays(rand(1, 30))->toDateString(),
            'total_points_earned' => $lifetimePoints,
            'total_points_redeemed' => $pointsRedeemed,
            'current_points_balance' => $currentPoints,
            'pending_points' => rand(0, 100),
            'expired_points' => rand(0, 500),
            'current_tier' => $tier['tier_name'],
            'tier_points' => $currentPoints,
            'next_tier_points' => $this->getNextTierThreshold($loyaltyProgram, $lifetimePoints),
            'total_transactions' => rand(5, 50),
            'total_spent' => rand(1000000, 50000000), // 1M-50M VND
            'average_transaction' => rand(200000, 2000000), // 200k-2M VND
            'visits_count' => rand(5, 50),
            'first_purchase_date' => Carbon::now()->subDays(rand(30, 180))->toDateString(),
            'last_purchase_date' => Carbon::now()->subDays(rand(1, 30))->toDateString(),
            'total_cashback_earned' => rand(10000, 500000), // 10k-500k VND
            'total_cashback_redeemed' => rand(0, 200000), // 0-200k VND
            'current_cashback_balance' => rand(0, 300000), // 0-300k VND
            'referrals_made' => rand(0, 10),
            'referral_points_earned' => rand(0, 1000),
            'email_notifications' => true,
            'sms_notifications' => rand(0, 1) === 1,
            'push_notifications' => true,
            'marketing_emails' => rand(0, 1) === 1,
            'preferences' => [
                'preferred_rewards' => $this->getRandomPreferredRewards(),
                'communication_preferences' => [
                    'email' => rand(0, 1) === 1,
                    'sms' => rand(0, 1) === 1,
                    'push' => rand(0, 1) === 1,
                ],
            ],
            'metadata' => [
                'enrollment_channel' => $this->getRandomEnrollmentChannel(),
                'created_via' => 'seeder',
            ],
        ]);

        $this->logSeedingProgress('loyalty_membership_created', [
            'membership_id' => $membership->id,
            'customer_id' => $customer->id,
            'program_id' => $loyaltyProgram->id,
            'tier' => $tier['tier_name'],
            'lifetime_points' => $lifetimePoints
        ]);

        return $membership;
    }

    /**
     * Create loyalty transactions history with point calculations
     */
    private function createLoyaltyTransactions(LoyaltyMembership $membership, int $count): void
    {
        $this->logSeedingProgress('creating_loyalty_transactions', [
            'membership_id' => $membership->id,
            'transaction_count' => $count
        ]);

        $currentBalance = 0;
        
        for ($i = 0; $i < $count; $i++) {
            $transactionDate = Carbon::now()->subDays(rand(1, 180));
            $transactionType = $this->getRandomTransactionType();
            $points = $this->getPointsForTransaction($transactionType);
            
            $balanceBefore = $currentBalance;
            $currentBalance = max(0, $currentBalance + $points); // Ensure balance doesn't go negative
            
            $this->createLoyaltyTransaction(
                $membership, 
                $transactionType, 
                $points, 
                $balanceBefore,
                $currentBalance,
                $transactionDate
            );
        }
    }

    /**
     * Create individual loyalty transaction with proper balance tracking
     */
    private function createLoyaltyTransaction(
        LoyaltyMembership $membership, 
        string $transactionType, 
        int $points,
        int $balanceBefore,
        int $balanceAfter,
        Carbon $transactionDate
    ): void {
        $orderAmount = $transactionType === 'earn' ? rand(500000, 5000000) : 0;
        $orderNumber = $transactionType === 'earn' ? 'SO' . str_pad(rand(1, 99999), 8, '0', STR_PAD_LEFT) : null;

        LoyaltyTransaction::create([
            'store_id' => $membership->store_id,
            'membership_id' => $membership->id,
            'customer_id' => $membership->customer_id,
            'transaction_number' => $this->generateTransactionNumber($transactionType),
            'type' => $transactionType,
            'reason' => $this->getTransactionReason($transactionType),
            'transaction_date' => $transactionDate,
            'points_change' => $points,
            'points_balance_before' => $balanceBefore,
            'points_balance_after' => $balanceAfter,
            'cashback_change' => 0,
            'cashback_balance_before' => 0,
            'cashback_balance_after' => 0,
            'order_number' => $orderNumber,
            'order_id' => $transactionType === 'earn' ? rand(1, 1000) : null,
            'order_type' => $transactionType === 'earn' ? 'sales_order' : null,
            'order_amount' => $orderAmount,
            'earning_rate' => $transactionType === 'earn' ? 1 : null,
            'multiplier' => 1,
            'points_expiry_date' => $transactionType === 'earn' ? 
                $transactionDate->copy()->addDays(365)->toDateString() : null,
            'status' => 'completed',
            'description' => $this->getTransactionDescription($transactionType, $points),
            'notes' => $this->getTransactionNotes($transactionType),
            'processed_by' => $membership->program->created_by ?? null,
            'metadata' => [
                'channel' => $this->getTransactionChannel(),
                'processed_by_system' => true,
                'created_via' => 'seeder',
                'tier_at_transaction' => $membership->current_tier,
            ],
        ]);
    }

    /**
     * Generate unique membership number
     */
    private function generateMembershipNumber(LoyaltyProgram $loyaltyProgram, Customer $customer): string
    {
        $programCode = substr($loyaltyProgram->code, 0, 3);
        $customerCode = str_pad($customer->id, 6, '0', STR_PAD_LEFT);
        $randomSuffix = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        return strtoupper($programCode) . $customerCode . $randomSuffix;
    }

    /**
     * Calculate customer tier based on loyalty program thresholds
     */
    private function calculateTier(LoyaltyProgram $loyaltyProgram, int $lifetimePoints): array
    {
        $tier = ['tier_name' => 'bronze', 'points_required' => 0, 'multiplier' => 1.0];
        
        if (!$loyaltyProgram->tier_config) {
            return $tier;
        }
        
        foreach ($loyaltyProgram->tier_config as $threshold) {
            if ($lifetimePoints >= $threshold['points_required']) {
                $tier = $threshold;
            } else {
                break;
            }
        }
        
        return $tier;
    }

    /**
     * Calculate tier progress percentage
     */
    private function calculateTierProgress(LoyaltyProgram $loyaltyProgram, int $lifetimePoints): float
    {
        $currentTier = $this->calculateTier($loyaltyProgram, $lifetimePoints);
        $nextTier = $this->getNextTier($loyaltyProgram, $lifetimePoints);
        
        if (!$nextTier) {
            return 100.0; // Max tier reached
        }
        
        $currentThreshold = $currentTier['points_required'];
        $nextThreshold = $nextTier['points_required'];
        
        if ($nextThreshold <= $currentThreshold) {
            return 100.0;
        }
        
        $progress = ($lifetimePoints - $currentThreshold) / ($nextThreshold - $currentThreshold) * 100;
        return min(100.0, max(0.0, $progress));
    }

    /**
     * Get next tier information
     */
    private function getNextTier(LoyaltyProgram $loyaltyProgram, int $lifetimePoints): ?array
    {
        foreach ($loyaltyProgram->tier_config as $threshold) {
            if ($lifetimePoints < $threshold['points_required']) {
                return $threshold;
            }
        }
        
        return null; // Already at max tier
    }

    /**
     * Get next tier threshold
     */
    private function getNextTierThreshold(LoyaltyProgram $loyaltyProgram, int $lifetimePoints): ?int
    {
        $nextTier = $this->getNextTier($loyaltyProgram, $lifetimePoints);
        return $nextTier ? $nextTier['points_required'] : null;
    }

    /**
     * Generate transaction number
     */
    private function generateTransactionNumber(string $transactionType): string
    {
        $prefix = match($transactionType) {
            'earn' => 'LTE',
            'redeem' => 'LTR',
            'expire' => 'LTX',
            'adjust' => 'LTA',
            'bonus' => 'LTB',
            'refund' => 'LTF',
            default => 'LT',
        };

        return $prefix . date('Ymd') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT) . rand(10, 99);
    }

    /**
     * Get transaction reason
     */
    private function getTransactionReason(string $transactionType): string
    {
        return match($transactionType) {
            'earn' => 'purchase',
            'redeem' => 'redemption',
            'expire' => 'expiry',
            'adjust' => 'manual_adjust',
            'bonus' => 'bonus',
            'refund' => 'return',
            default => 'purchase',
        };
    }

    /**
     * Get random enrollment channel
     */
    private function getRandomEnrollmentChannel(): string
    {
        $channels = ['pos', 'online', 'mobile_app', 'in_store', 'call_center'];
        return $channels[array_rand($channels)];
    }

    /**
     * Get random preferred rewards
     */
    private function getRandomPreferredRewards(): array
    {
        $allRewards = ['discount', 'free_shipping', 'exclusive_offers', 'early_access', 'birthday_bonus'];
        $count = rand(1, 3);
        return array_slice($allRewards, 0, $count);
    }

    /**
     * Get random transaction type with weighted distribution
     */
    private function getRandomTransactionType(): string
    {
        $types = ['earn', 'redeem', 'bonus', 'adjust'];
        $weights = [60, 25, 10, 5]; // 60% earn, 25% redeem, 10% bonus, 5% adjust

        $random = rand(1, 100);
        $cumulative = 0;

        foreach ($types as $index => $type) {
            $cumulative += $weights[$index];
            if ($random <= $cumulative) {
                return $type;
            }
        }

        return 'earn';
    }

    /**
     * Get points amount for transaction type
     */
    private function getPointsForTransaction(string $transactionType): int
    {
        return match($transactionType) {
            'earn' => rand(10, 500),
            'redeem' => -rand(50, 300),
            'bonus' => rand(50, 200),
            'adjust' => rand(-100, 100),
            'expire' => -rand(10, 100),
            'refund' => rand(10, 200),
            default => rand(10, 100),
        };
    }

    /**
     * Get transaction description in Vietnamese
     */
    private function getTransactionDescription(string $transactionType, int $points): string
    {
        $sign = $points >= 0 ? '+' : '';
        $formattedPoints = $sign . number_format(abs($points));
        
        return match($transactionType) {
            'earn' => "Tích điểm từ mua hàng ({$formattedPoints} điểm)",
            'redeem' => "Đổi điểm lấy quà ({$formattedPoints} điểm)",
            'bonus' => "Điểm thưởng ({$formattedPoints} điểm)",
            'adjust' => "Điều chỉnh điểm ({$formattedPoints} điểm)",
            'expire' => "Điểm hết hạn ({$formattedPoints} điểm)",
            'refund' => "Hoàn điểm từ trả hàng ({$formattedPoints} điểm)",
            default => "Giao dịch điểm ({$formattedPoints} điểm)",
        };
    }

    /**
     * Get transaction notes with Vietnamese context
     */
    private function getTransactionNotes(string $transactionType): ?string
    {
        $notes = [
            'earn' => [
                'Mua hàng tại cửa hàng', 
                'Đơn hàng online', 
                'Mua hàng qua ứng dụng mobile',
                'Thanh toán qua POS',
                'Giao dịch tại quầy'
            ],
            'redeem' => [
                'Đổi voucher giảm giá', 
                'Đổi quà tặng', 
                'Đổi phiếu mua hàng',
                'Sử dụng điểm thanh toán',
                'Đổi sản phẩm khuyến mãi'
            ],
            'bonus' => [
                'Điểm thưởng sinh nhật', 
                'Điểm giới thiệu bạn bè', 
                'Điểm khuyến mãi đặc biệt',
                'Thưởng thành viên mới',
                'Điểm thưởng sự kiện'
            ],
            'adjust' => [
                'Điều chỉnh do lỗi hệ thống', 
                'Bù trừ điểm', 
                'Điều chỉnh thủ công',
                'Khiếu nại khách hàng',
                'Cập nhật số dư'
            ],
        ];

        $typeNotes = $notes[$transactionType] ?? ['Giao dịch điểm'];
        return $typeNotes[array_rand($typeNotes)];
    }

    /**
     * Get transaction channel
     */
    private function getTransactionChannel(): string
    {
        $channels = ['pos', 'online', 'mobile_app', 'call_center', 'in_store'];
        return $channels[array_rand($channels)];
    }
}
