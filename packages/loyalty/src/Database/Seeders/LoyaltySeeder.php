<?php

namespace Packages\Loyalty\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Loyalty\Models\LoyaltyProgram;
use Packages\Loyalty\Models\LoyaltyMembership;
use Packages\Loyalty\Models\LoyaltyTransaction;
use Packages\Store\Models\Store;
use Packages\Customer\Models\Customer;
use Packages\User\Models\User;
use Carbon\Carbon;

class LoyaltySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('⭐ Seeding Loyalty Programs...');

        $stores = Store::all();
        
        foreach ($stores as $store) {
            // Create loyalty program for each store
            $loyaltyProgram = $this->createLoyaltyProgram($store);
            
            // Create memberships for customers
            $customers = Customer::where('store_id', $store->id)->limit(20)->get();
            foreach ($customers as $customer) {
                $membership = $this->createLoyaltyMembership($loyaltyProgram, $customer);
                
                // Create some transactions for each membership
                $this->createLoyaltyTransactions($membership, rand(3, 10));
            }
        }

        $this->command->info('✅ Loyalty Programs seeded successfully!');
    }

    private function createLoyaltyProgram(Store $store): LoyaltyProgram
    {
        $createdBy = $store->users()->first();

        return LoyaltyProgram::create([
            'store_id' => $store->id,
            'name' => 'Chương trình khách hàng thân thiết',
            'code' => 'LOYALTY_' . strtoupper($store->slug),
            'description' => 'Tích điểm mỗi khi mua hàng và đổi quà hấp dẫn',
            'type' => 'points',
            'status' => 'active',
            'auto_enrollment' => true,
            'earn_rate' => 1, // 1 point per 1 VND
            'redeem_rate' => 1000, // 1000 VND per point
            'min_points_to_redeem' => 100,
            'max_points_per_transaction' => 1000,
            'points_expiry_days' => 365, // 1 year
            'tier_config' => json_encode([
                'bronze' => ['min_points' => 0, 'multiplier' => 1.0],
                'silver' => ['min_points' => 1000, 'multiplier' => 1.2],
                'gold' => ['min_points' => 5000, 'multiplier' => 1.5],
                'platinum' => ['min_points' => 10000, 'multiplier' => 2.0],
            ]),
            'tier_based_earning' => true,
            'tier_benefits' => json_encode([
                'bronze' => ['discount' => 0, 'free_shipping' => false],
                'silver' => ['discount' => 5, 'free_shipping' => false],
                'gold' => ['discount' => 10, 'free_shipping' => true],
                'platinum' => ['discount' => 15, 'free_shipping' => true],
            ]),
            'start_date' => Carbon::now()->subMonths(6)->toDateString(),
            'end_date' => Carbon::now()->addYears(2)->toDateString(),
            'min_purchase_amount' => 50000, // 50k VND minimum
            'welcome_bonus' => true,
            'welcome_bonus_points' => 100,
            'referral_bonus' => true,
            'referral_bonus_points' => 200,
            'birthday_bonus' => true,
            'birthday_bonus_points' => 500,
            'terms_conditions' => 'Điều khoản và điều kiện chương trình khách hàng thân thiết',
            'created_by' => $createdBy?->id,
            'metadata' => json_encode([
                'created_via' => 'seeder',
                'program_version' => '1.0',
            ]),
        ]);
    }

    private function createLoyaltyMembership(LoyaltyProgram $loyaltyProgram, Customer $customer): LoyaltyMembership
    {
        $joinDate = Carbon::now()->subDays(rand(30, 365));
        $totalPoints = rand(100, 5000);
        $availablePoints = intval($totalPoints * 0.8); // 80% available

        return LoyaltyMembership::create([
            'store_id' => $loyaltyProgram->store_id,
            'program_id' => $loyaltyProgram->id,
            'customer_id' => $customer->id,
            'membership_number' => $this->generateMembershipNumber($loyaltyProgram, $customer),
            'status' => 'active',
            'enrolled_date' => $joinDate->toDateString(),
            'last_activity_date' => Carbon::now()->subDays(rand(1, 30))->toDateString(),
            'total_points_earned' => $totalPoints,
            'total_points_redeemed' => $totalPoints - $availablePoints,
            'current_points_balance' => $availablePoints,
            'pending_points' => rand(0, 100),
            'expired_points' => rand(0, 500),
            'current_tier' => $this->calculateTier($totalPoints),
            'tier_points' => rand(0, 1000),
            'next_tier_points' => $this->getNextTierThreshold($totalPoints),
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
            'metadata' => json_encode([
                'enrollment_channel' => 'pos',
                'preferred_rewards' => ['discount', 'free_shipping'],
            ]),
        ]);
    }

    private function createLoyaltyTransactions(LoyaltyMembership $membership, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $transactionDate = Carbon::now()->subDays(rand(1, 90));
            $transactionType = $this->getRandomTransactionType();
            $points = $this->getPointsForTransaction($transactionType);

            $this->createLoyaltyTransaction($membership, $transactionType, $points, $transactionDate);
        }
    }

    private function createLoyaltyTransaction(LoyaltyMembership $membership, string $transactionType, int $points, Carbon $transactionDate): void
    {
        $balanceBefore = rand(0, 5000);
        $balanceAfter = $balanceBefore + $points;
        $orderAmount = $transactionType === 'earn' ? rand(500000, 5000000) : 0;

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
            'order_number' => $transactionType === 'earn' ? 'SO' . str_pad(rand(1, 99999), 8, '0', STR_PAD_LEFT) : null,
            'order_id' => $transactionType === 'earn' ? rand(1, 1000) : null,
            'order_type' => $transactionType === 'earn' ? 'sales_order' : null,
            'order_amount' => $orderAmount,
            'description' => $this->getTransactionDescription($transactionType, $points),
            'points_expiry_date' => $transactionType === 'earn' ?
                $transactionDate->copy()->addDays(365)->toDateString() : null,
            'status' => 'completed',
            'notes' => $this->getTransactionNotes($transactionType),
            'metadata' => json_encode([
                'channel' => $this->getTransactionChannel(),
                'processed_by' => 'system',
            ]),
        ]);
    }

    private function generateMembershipNumber(LoyaltyProgram $loyaltyProgram, Customer $customer): string
    {
        $programCode = substr($loyaltyProgram->code, 0, 3);
        $customerCode = str_pad($customer->id, 6, '0', STR_PAD_LEFT);

        return strtoupper($programCode) . $customerCode . rand(100, 999);
    }

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

    private function calculateTier(int $totalPoints): string
    {
        if ($totalPoints >= 10000) return 'platinum';
        if ($totalPoints >= 5000) return 'gold';
        if ($totalPoints >= 1000) return 'silver';
        return 'bronze';
    }

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

    private function getTransactionDescription(string $transactionType, int $points): string
    {
        return match($transactionType) {
            'earn' => "Tích điểm từ mua hàng (+{$points} điểm)",
            'redeem' => "Đổi điểm lấy quà ({$points} điểm)",
            'bonus' => "Điểm thưởng (+{$points} điểm)",
            'adjust' => "Điều chỉnh điểm ({$points} điểm)",
            'expire' => "Điểm hết hạn ({$points} điểm)",
            'refund' => "Hoàn điểm từ trả hàng (+{$points} điểm)",
            default => "Giao dịch điểm ({$points} điểm)",
        };
    }

    private function getTransactionNotes(string $transactionType): ?string
    {
        $notes = [
            'earn' => ['Mua hàng tại cửa hàng', 'Đơn hàng online', 'Mua hàng qua app'],
            'redeem' => ['Đổi voucher giảm giá', 'Đổi quà tặng', 'Đổi phiếu mua hàng'],
            'bonus' => ['Điểm thưởng sinh nhật', 'Điểm giới thiệu bạn bè', 'Điểm khuyến mãi'],
            'adjust' => ['Điều chỉnh do lỗi hệ thống', 'Bù trừ điểm', 'Điều chỉnh thủ công'],
        ];

        return $notes[$transactionType][array_rand($notes[$transactionType])] ?? null;
    }

    private function getTransactionChannel(): string
    {
        $channels = ['pos', 'online', 'mobile_app', 'call_center'];
        return $channels[array_rand($channels)];
    }

    private function getNextTierThreshold(int $totalPoints): int
    {
        $tiers = [
            'bronze' => 0,
            'silver' => 1000,
            'gold' => 5000,
            'platinum' => 10000,
        ];

        foreach ($tiers as $tier => $threshold) {
            if ($totalPoints < $threshold) {
                return $threshold;
            }
        }

        return 15000; // Next level after platinum
    }
}
