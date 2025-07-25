<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Reports\Models\ReportTemplate;
use Packages\Promotions\Models\Promotion;
use Packages\Loyalty\Models\LoyaltyProgram;
use Packages\Loyalty\Models\LoyaltyMembership;
use Packages\Payments\Models\PaymentMethod;
use Packages\Notifications\Models\NotificationTemplate;
use Packages\Store\Models\Store;
use Packages\Customer\Models\Customer;

class AdvancedFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::all();

        foreach ($stores as $store) {
            $this->createReportTemplates($store);
            $this->createPromotions($store);
            $this->createLoyaltyPrograms($store);
            $this->createPaymentMethods($store);
            $this->createNotificationTemplates($store);
        }
    }

    private function createReportTemplates($store)
    {
        $templates = [
            [
                'code' => 'DAILY_SALES',
                'name' => 'Báo cáo Doanh thu Hàng ngày',
                'category' => 'sales',
                'type' => 'summary',
                'frequency' => 'daily',
                'data_sources' => ['sales_orders', 'invoices'],
                'columns' => [
                    ['name' => 'date', 'label' => 'Ngày', 'type' => 'date'],
                    ['name' => 'total_orders', 'label' => 'Số đơn hàng', 'type' => 'number'],
                    ['name' => 'total_revenue', 'label' => 'Doanh thu', 'type' => 'currency'],
                    ['name' => 'avg_order_value', 'label' => 'Giá trị TB/đơn', 'type' => 'currency'],
                ],
                'is_system' => true,
            ],
            [
                'code' => 'INVENTORY_REPORT',
                'name' => 'Báo cáo Tồn kho',
                'category' => 'inventory',
                'type' => 'detail',
                'frequency' => 'weekly',
                'data_sources' => ['products', 'material_inventory'],
                'columns' => [
                    ['name' => 'product_name', 'label' => 'Tên sản phẩm', 'type' => 'text'],
                    ['name' => 'current_stock', 'label' => 'Tồn kho hiện tại', 'type' => 'number'],
                    ['name' => 'min_stock', 'label' => 'Tồn kho tối thiểu', 'type' => 'number'],
                    ['name' => 'stock_value', 'label' => 'Giá trị tồn kho', 'type' => 'currency'],
                ],
                'is_system' => true,
            ],
            [
                'code' => 'CUSTOMER_ANALYSIS',
                'name' => 'Phân tích Khách hàng',
                'category' => 'customer',
                'type' => 'summary',
                'frequency' => 'monthly',
                'data_sources' => ['customers', 'sales_orders'],
                'columns' => [
                    ['name' => 'customer_group', 'label' => 'Nhóm khách hàng', 'type' => 'text'],
                    ['name' => 'total_customers', 'label' => 'Số khách hàng', 'type' => 'number'],
                    ['name' => 'total_spent', 'label' => 'Tổng chi tiêu', 'type' => 'currency'],
                    ['name' => 'avg_spent', 'label' => 'Chi tiêu TB', 'type' => 'currency'],
                ],
                'is_system' => true,
            ],
        ];

        foreach ($templates as $templateData) {
            ReportTemplate::firstOrCreate(
                [
                    'store_id' => $store->id,
                    'code' => $templateData['code'],
                ],
                array_merge($templateData, [
                    'store_id' => $store->id,
                    'output_format' => 'table',
                    'is_active' => true,
                    'is_public' => true,
                    'cache_enabled' => true,
                    'cache_duration' => 30,
                    'created_by' => 1,
                ])
            );
        }
    }

    private function createPromotions($store)
    {
        $promotions = [
            [
                'code' => 'SUMMER2024',
                'name' => 'Khuyến mãi Hè 2024',
                'description' => 'Giảm giá 15% cho tất cả sản phẩm',
                'type' => 'percentage',
                'target' => 'order',
                'discount_value' => 15,
                'min_order_amount' => 500000,
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(30),
                'usage_limit' => 1000,
                'status' => 'active',
            ],
            [
                'code' => 'NEWCUSTOMER',
                'name' => 'Ưu đãi Khách hàng Mới',
                'description' => 'Giảm 100,000đ cho đơn hàng đầu tiên',
                'type' => 'fixed_amount',
                'target' => 'order',
                'discount_value' => 100000,
                'min_order_amount' => 1000000,
                'start_date' => now()->subDays(60),
                'end_date' => now()->addDays(60),
                'new_customers_only' => true,
                'usage_limit_per_customer' => 1,
                'status' => 'active',
            ],
            [
                'code' => 'BUY2GET1',
                'name' => 'Mua 2 Tặng 1',
                'description' => 'Mua 2 sản phẩm tặng 1 sản phẩm cùng loại',
                'type' => 'buy_x_get_y',
                'target' => 'product',
                'buy_quantity' => 2,
                'get_quantity' => 1,
                'get_free' => true,
                'start_date' => now()->subDays(15),
                'end_date' => now()->addDays(15),
                'status' => 'active',
            ],
        ];

        foreach ($promotions as $promotionData) {
            Promotion::firstOrCreate(
                [
                    'store_id' => $store->id,
                    'code' => $promotionData['code'],
                ],
                array_merge($promotionData, [
                    'store_id' => $store->id,
                    'combinable' => false,
                    'priority' => 1,
                    'show_on_website' => true,
                    'show_in_app' => true,
                    'created_by' => 1,
                ])
            );
        }
    }

    private function createLoyaltyPrograms($store)
    {
        $program = LoyaltyProgram::firstOrCreate(
            [
                'store_id' => $store->id,
                'code' => 'BOXPOS_LOYALTY',
            ],
            [
            'store_id' => $store->id,
            'code' => 'BOXPOS_LOYALTY',
            'name' => 'Chương trình Khách hàng Thân thiết BoxPos',
            'description' => 'Tích điểm và nhận ưu đãi hấp dẫn',
            'type' => 'points',
            'status' => 'active',
            'auto_enrollment' => true,
            'earn_rate' => 0.01, // 1 điểm cho 100 VND
            'redeem_rate' => 100, // 1 điểm = 100 VND
            'min_points_to_redeem' => 100,
            'points_expiry_days' => 365,
            'start_date' => now()->subMonths(6),
            'welcome_bonus' => true,
            'welcome_bonus_points' => 100,
            'birthday_bonus' => true,
            'birthday_bonus_points' => 500,
            'referral_bonus' => true,
            'referral_bonus_points' => 1000,
            'tier_config' => json_encode([
                'bronze' => ['min_spent' => 0, 'earn_multiplier' => 1],
                'silver' => ['min_spent' => 5000000, 'earn_multiplier' => 1.2],
                'gold' => ['min_spent' => 20000000, 'earn_multiplier' => 1.5],
                'platinum' => ['min_spent' => 50000000, 'earn_multiplier' => 2],
            ]),
            'tier_based_earning' => true,
            'created_by' => 1,
            ]
        );

        // Create memberships for existing customers
        $customers = Customer::where('store_id', $store->id)->get();
        foreach ($customers as $customer) {
            $totalSpent = $customer->total_spent ?? 0;
            $tier = 'bronze';
            if ($totalSpent >= 50000000) $tier = 'platinum';
            elseif ($totalSpent >= 20000000) $tier = 'gold';
            elseif ($totalSpent >= 5000000) $tier = 'silver';

            $pointsEarned = intval($totalSpent * 0.01);

            LoyaltyMembership::firstOrCreate(
                [
                    'store_id' => $store->id,
                    'program_id' => $program->id,
                    'customer_id' => $customer->id,
                ],
                [
                'store_id' => $store->id,
                'program_id' => $program->id,
                'customer_id' => $customer->id,
                'membership_number' => 'LP' . str_pad($customer->id, 8, '0', STR_PAD_LEFT),
                'status' => 'active',
                'enrolled_date' => now()->subMonths(rand(1, 6)),
                'total_points_earned' => $pointsEarned,
                'current_points_balance' => intval($pointsEarned * 0.8), // 80% remaining
                'total_points_redeemed' => intval($pointsEarned * 0.2), // 20% used
                'current_tier' => $tier,
                'tier_achieved_date' => now()->subMonths(rand(1, 3)),
                'total_transactions' => $customer->total_orders ?? 0,
                'total_spent' => $totalSpent,
                'average_transaction' => $totalSpent > 0 && $customer->total_orders > 0 
                    ? $totalSpent / $customer->total_orders : 0,
                'visits_count' => $customer->total_orders ?? 0,
                'last_activity_date' => $customer->last_order_date,
                'first_purchase_date' => now()->subMonths(rand(6, 24)),
                'last_purchase_date' => $customer->last_order_date,
                ]
            );
        }
    }

    private function createPaymentMethods($store)
    {
        $paymentMethods = [
            [
                'code' => 'CASH',
                'name' => 'Tiền mặt',
                'type' => 'cash',
                'category' => 'instant',
                'is_default' => true,
                'icon' => '/icons/cash.svg',
                'color' => '#28a745',
                'sort_order' => 1,
            ],
            [
                'code' => 'BANK_TRANSFER',
                'name' => 'Chuyển khoản ngân hàng',
                'type' => 'bank_transfer',
                'category' => 'delayed',
                'requires_verification' => true,
                'icon' => '/icons/bank.svg',
                'color' => '#007bff',
                'sort_order' => 2,
            ],
            [
                'code' => 'VISA_CARD',
                'name' => 'Thẻ Visa',
                'type' => 'card',
                'category' => 'instant',
                'provider' => 'Visa',
                'processing_fee_percent' => 2.5,
                'icon' => '/icons/visa.svg',
                'color' => '#1a1f71',
                'sort_order' => 3,
            ],
            [
                'code' => 'MOMO',
                'name' => 'Ví MoMo',
                'type' => 'e_wallet',
                'category' => 'instant',
                'provider' => 'MoMo',
                'processing_fee_percent' => 1.5,
                'icon' => '/icons/momo.svg',
                'color' => '#d82d8b',
                'sort_order' => 4,
            ],
            [
                'code' => 'ZALOPAY',
                'name' => 'ZaloPay',
                'type' => 'e_wallet',
                'category' => 'instant',
                'provider' => 'ZaloPay',
                'processing_fee_percent' => 1.8,
                'icon' => '/icons/zalopay.svg',
                'color' => '#0068ff',
                'sort_order' => 5,
            ],
        ];

        foreach ($paymentMethods as $methodData) {
            PaymentMethod::firstOrCreate(
                [
                    'store_id' => $store->id,
                    'code' => $methodData['code'],
                ],
                array_merge($methodData, [
                'store_id' => $store->id,
                'currency' => 'VND',
                'min_amount' => 1000,
                'max_amount' => $methodData['type'] === 'cash' ? 100000000 : 500000000,
                'is_active' => true,
                'show_on_pos' => true,
                'show_on_website' => true,
                'show_on_mobile' => true,
                'supports_refund' => true,
                'supports_partial_refund' => true,
                'refund_days_limit' => 30,
                'created_by' => 1,
                ])
            );
        }
    }

    private function createNotificationTemplates($store)
    {
        $templates = [
            [
                'code' => 'ORDER_CONFIRMED',
                'name' => 'Xác nhận Đơn hàng',
                'type' => 'email',
                'category' => 'order',
                'trigger' => 'order_confirmed',
                'subject' => 'Đơn hàng #{order_number} đã được xác nhận',
                'content' => 'Xin chào {customer_name}, đơn hàng #{order_number} của bạn đã được xác nhận và đang được xử lý.',
                'send_to_customer' => true,
            ],
            [
                'code' => 'PAYMENT_RECEIVED',
                'name' => 'Thanh toán Thành công',
                'type' => 'sms',
                'category' => 'payment',
                'trigger' => 'payment_received',
                'content' => 'BoxPos: Thanh toán {amount} cho đơn hàng #{order_number} đã thành công. Cảm ơn bạn!',
                'send_to_customer' => true,
            ],
            [
                'code' => 'LOW_STOCK_ALERT',
                'name' => 'Cảnh báo Hết hàng',
                'type' => 'in_app',
                'category' => 'inventory',
                'trigger' => 'low_stock',
                'subject' => 'Cảnh báo: Sản phẩm sắp hết hàng',
                'content' => 'Sản phẩm {product_name} chỉ còn {current_stock} {unit} trong kho.',
                'send_to_admin' => true,
                'admin_roles' => json_encode(['admin', 'manager']),
            ],
            [
                'code' => 'CUSTOMER_BIRTHDAY',
                'name' => 'Chúc mừng Sinh nhật',
                'type' => 'email',
                'category' => 'customer',
                'trigger' => 'customer_birthday',
                'subject' => 'Chúc mừng sinh nhật! 🎉',
                'content' => 'Chúc mừng sinh nhật {customer_name}! Nhận ngay ưu đãi đặc biệt dành cho bạn.',
                'send_to_customer' => true,
            ],
            [
                'code' => 'LOYALTY_POINTS_EARNED',
                'name' => 'Tích điểm Thành công',
                'type' => 'push',
                'category' => 'customer',
                'trigger' => 'loyalty_points_earned',
                'subject' => 'Bạn vừa nhận được {points} điểm!',
                'content' => 'Chúc mừng! Bạn vừa nhận được {points} điểm từ đơn hàng #{order_number}.',
                'send_to_customer' => true,
            ],
        ];

        foreach ($templates as $templateData) {
            NotificationTemplate::firstOrCreate(
                [
                    'store_id' => $store->id,
                    'code' => $templateData['code'],
                ],
                array_merge($templateData, [
                    'store_id' => $store->id,
                    'is_active' => true,
                    'is_system' => true,
                    'priority' => 5,
                    'delay_minutes' => 0,
                    'respect_quiet_hours' => true,
                    'quiet_hours_start' => '22:00',
                    'quiet_hours_end' => '07:00',
                    'created_by' => 1,
                ])
            );
        }
    }
}
