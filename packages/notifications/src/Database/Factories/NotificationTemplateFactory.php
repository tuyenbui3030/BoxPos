<?php

namespace Packages\Notifications\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Notifications\Models\NotificationTemplate;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Notifications\Models\NotificationTemplate>
 */
class NotificationTemplateFactory extends Factory
{
    protected $model = NotificationTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $templateType = $this->faker->randomElement(['email', 'sms', 'push', 'in_app']);
        
        return [
            'code' => $this->faker->unique()->regexify('TPL[0-9]{6}'),
            'name' => $this->getVietnameseTemplateName(),
            'description' => $this->faker->optional()->paragraph(),
            'type' => $templateType,
            'category' => $this->faker->randomElement(['order', 'payment', 'promotion', 'system', 'customer', 'inventory']),
            'trigger' => $this->faker->randomElement(['manual', 'automatic', 'scheduled', 'event']),
            'subject' => $this->getVietnameseSubject($templateType),
            'content' => $this->getVietnameseContent($templateType),
            'content_html' => $this->getVietnameseContent($templateType),
            'placeholders' => $this->getTemplateVariables(),
            'delivery_channels' => [$templateType],
            'priority' => $this->faker->randomElement(['low', 'normal', 'high', 'urgent']),
            'is_active' => true,
            'schedule_config' => [],
            'retry_config' => [
                'max_attempts' => $this->faker->numberBetween(1, 5),
                'delay_minutes' => $this->faker->numberBetween(5, 60)
            ],
            'rate_limit' => null,
            'conditions' => [],
            'metadata' => $this->getTemplateMetadata(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create email template
     */
    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'email',
            'subject' => $this->getVietnameseEmailSubject(),
            'content' => $this->getVietnameseEmailContent(),
            'sender_name' => $this->faker->company(),
            'sender_email' => $this->faker->companyEmail(),
            'reply_to' => $this->faker->companyEmail(),
        ]);
    }

    /**
     * Create SMS template
     */
    public function sms(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sms',
            'subject' => null,
            'content' => $this->getVietnameseSmsContent(),
            'sender_name' => $this->faker->company(),
        ]);
    }

    /**
     * Create push notification template
     */
    public function push(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'push',
            'subject' => $this->getVietnamesePushTitle(),
            'content' => $this->getVietnamesePushContent(),
        ]);
    }

    /**
     * Create in-app notification template
     */
    public function inApp(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'in_app',
            'subject' => $this->getVietnameseInAppTitle(),
            'content' => $this->getVietnameseInAppContent(),
        ]);
    }

    /**
     * Create active template
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create default template
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Create order category template
     */
    public function orderCategory(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'order',
            'template_name' => $this->getVietnameseOrderTemplateName(),
        ]);
    }

    /**
     * Create promotion category template
     */
    public function promotionCategory(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'promotion',
            'template_name' => $this->getVietnamesePromotionTemplateName(),
        ]);
    }

    /**
     * Get Vietnamese template names
     */
    private function getVietnameseTemplateName(): string
    {
        $names = [
            'Xác nhận đơn hàng',
            'Thông báo thanh toán',
            'Khuyến mãi đặc biệt',
            'Chào mừng khách hàng mới',
            'Nhắc nhở thanh toán',
            'Xác nhận giao hàng',
            'Thông báo hết hàng',
            'Cảm ơn khách hàng',
            'Thông báo bảo trì hệ thống',
            'Sinh nhật khách hàng',
            'Thông báo khuyến mãi',
            'Xác nhận hủy đơn',
            'Thông báo hoàn tiền',
            'Nhắc nhở đánh giá',
            'Thông báo sự kiện'
        ];
        
        return $this->faker->randomElement($names);
    }

    /**
     * Get Vietnamese subjects based on type
     */
    private function getVietnameseSubject(string $type): ?string
    {
        if ($type === 'sms') {
            return null;
        }
        
        $subjects = [
            'Xác nhận đơn hàng #{{order_number}}',
            'Cảm ơn bạn đã mua hàng tại {{store_name}}',
            'Khuyến mãi đặc biệt dành cho {{customer_name}}',
            'Thông báo giao hàng thành công',
            'Nhắc nhở thanh toán đơn hàng #{{order_number}}',
            'Chào mừng {{customer_name}} đến với {{store_name}}',
            'Thông báo hết hàng: {{product_name}}',
            'Sinh nhật vui vẻ {{customer_name}}!',
            'Thông báo bảo trì hệ thống',
            'Đánh giá đơn hàng #{{order_number}}'
        ];
        
        return $this->faker->randomElement($subjects);
    }

    /**
     * Get Vietnamese content based on type
     */
    private function getVietnameseContent(string $type): string
    {
        switch ($type) {
            case 'email':
                return $this->getVietnameseEmailContent();
            case 'sms':
                return $this->getVietnameseSmsContent();
            case 'push':
                return $this->getVietnamesePushContent();
            case 'in_app':
                return $this->getVietnameseInAppContent();
            default:
                return $this->faker->paragraph();
        }
    }

    /**
     * Get Vietnamese email subjects
     */
    private function getVietnameseEmailSubject(): string
    {
        $subjects = [
            'Xác nhận đơn hàng #{{order_number}} - {{store_name}}',
            'Cảm ơn {{customer_name}} đã mua hàng',
            'Khuyến mãi đặc biệt - Giảm {{discount}}% cho đơn hàng tiếp theo',
            'Thông báo giao hàng thành công #{{order_number}}',
            'Nhắc nhở thanh toán - Đơn hàng #{{order_number}}',
            'Chào mừng {{customer_name}} đến với {{store_name}}',
            'Thông báo hết hàng: {{product_name}}',
            'Sinh nhật vui vẻ {{customer_name}} - Ưu đãi đặc biệt!',
            'Thông báo bảo trì hệ thống vào {{maintenance_date}}',
            'Mời {{customer_name}} đánh giá đơn hàng #{{order_number}}'
        ];
        
        return $this->faker->randomElement($subjects);
    }

    /**
     * Get Vietnamese email content
     */
    private function getVietnameseEmailContent(): string
    {
        $contents = [
            'Kính chào {{customer_name}},\n\nCảm ơn bạn đã đặt hàng tại {{store_name}}. Đơn hàng #{{order_number}} của bạn đã được xác nhận và đang được xử lý.\n\nThông tin đơn hàng:\n- Tổng tiền: {{total_amount}}\n- Ngày giao hàng dự kiến: {{delivery_date}}\n\nTrân trọng,\n{{store_name}}',
            'Xin chào {{customer_name}},\n\nĐơn hàng #{{order_number}} của bạn đã được giao thành công.\n\nNếu có bất kỳ vấn đề gì, vui lòng liên hệ với chúng tôi qua số điện thoại {{phone}} hoặc email {{email}}.\n\nCảm ơn bạn đã tin tưởng {{store_name}}!',
            'Chào {{customer_name}},\n\nNhân dịp sinh nhật của bạn, {{store_name}} xin gửi tặng mã giảm giá {{discount_code}} với ưu đãi {{discount}}%.\n\nMã có hiệu lực đến {{expiry_date}}.\n\nChúc bạn sinh nhật vui vẻ!'
        ];
        
        return $this->faker->randomElement($contents);
    }

    /**
     * Get Vietnamese SMS content
     */
    private function getVietnameseSmsContent(): string
    {
        $contents = [
            '{{store_name}}: Đơn hàng #{{order_number}} đã được xác nhận. Tổng tiền: {{total_amount}}. Cảm ơn bạn!',
            '{{store_name}}: Đơn hàng #{{order_number}} đã giao thành công. Cảm ơn bạn đã mua hàng!',
            '{{store_name}}: Khuyến mãi đặc biệt! Giảm {{discount}}% với mã {{discount_code}}. HSD: {{expiry_date}}',
            '{{store_name}}: Nhắc nhở thanh toán đơn hàng #{{order_number}}. Tổng tiền: {{total_amount}}',
            '{{store_name}}: Chào mừng {{customer_name}}! Tặng mã giảm giá {{discount_code}} cho lần mua đầu tiên.'
        ];
        
        return $this->faker->randomElement($contents);
    }

    /**
     * Get Vietnamese push titles
     */
    private function getVietnamesePushTitle(): string
    {
        $titles = [
            'Đơn hàng đã xác nhận',
            'Giao hàng thành công',
            'Khuyến mãi đặc biệt',
            'Nhắc nhở thanh toán',
            'Chào mừng thành viên mới',
            'Sinh nhật vui vẻ',
            'Sản phẩm mới',
            'Flash Sale',
            'Hết hàng',
            'Đánh giá đơn hàng'
        ];
        
        return $this->faker->randomElement($titles);
    }

    /**
     * Get Vietnamese push content
     */
    private function getVietnamesePushContent(): string
    {
        $contents = [
            'Đơn hàng #{{order_number}} đã được xác nhận. Cảm ơn bạn!',
            'Đơn hàng #{{order_number}} đã giao thành công.',
            'Giảm {{discount}}% cho đơn hàng tiếp theo. Mã: {{discount_code}}',
            'Vui lòng thanh toán đơn hàng #{{order_number}}',
            'Chào mừng {{customer_name}} đến với {{store_name}}!',
            'Sinh nhật vui vẻ! Tặng ưu đãi {{discount}}%',
            'Sản phẩm mới {{product_name}} đã có mặt',
            'Flash Sale - Giảm đến {{discount}}% chỉ trong hôm nay!',
            '{{product_name}} sắp hết hàng. Đặt ngay!',
            'Đánh giá đơn hàng #{{order_number}} để nhận điểm thưởng'
        ];
        
        return $this->faker->randomElement($contents);
    }

    /**
     * Get Vietnamese in-app titles
     */
    private function getVietnameseInAppTitle(): string
    {
        $titles = [
            'Đơn hàng mới',
            'Thanh toán thành công',
            'Khuyến mãi hot',
            'Thông báo hệ thống',
            'Cập nhật đơn hàng',
            'Ưu đãi đặc biệt',
            'Sản phẩm yêu thích',
            'Điểm thưởng',
            'Giao hàng',
            'Đánh giá'
        ];
        
        return $this->faker->randomElement($titles);
    }

    /**
     * Get Vietnamese in-app content
     */
    private function getVietnameseInAppContent(): string
    {
        $contents = [
            'Bạn có đơn hàng mới #{{order_number}} cần xử lý',
            'Thanh toán đơn hàng #{{order_number}} thành công',
            'Khuyến mãi {{discount}}% cho tất cả sản phẩm',
            'Hệ thống sẽ bảo trì vào {{maintenance_date}}',
            'Đơn hàng #{{order_number}} đã được cập nhật trạng thái',
            'Ưu đãi đặc biệt dành riêng cho bạn',
            'Sản phẩm yêu thích {{product_name}} đã có hàng',
            'Bạn có {{points}} điểm thưởng chưa sử dụng',
            'Đơn hàng #{{order_number}} đang được giao',
            'Mời bạn đánh giá đơn hàng #{{order_number}}'
        ];
        
        return $this->faker->randomElement($contents);
    }

    /**
     * Get template variables
     */
    private function getTemplateVariables(): array
    {
        return [
            'customer_name' => 'Tên khách hàng',
            'store_name' => 'Tên cửa hàng',
            'order_number' => 'Số đơn hàng',
            'total_amount' => 'Tổng tiền',
            'discount' => 'Phần trăm giảm giá',
            'discount_code' => 'Mã giảm giá',
            'product_name' => 'Tên sản phẩm',
            'delivery_date' => 'Ngày giao hàng',
            'expiry_date' => 'Ngày hết hạn',
            'phone' => 'Số điện thoại',
            'email' => 'Email',
            'points' => 'Điểm thưởng',
            'maintenance_date' => 'Ngày bảo trì'
        ];
    }

    /**
     * Get Vietnamese order template names
     */
    private function getVietnameseOrderTemplateName(): string
    {
        $names = [
            'Xác nhận đơn hàng',
            'Thông báo giao hàng',
            'Hủy đơn hàng',
            'Hoàn tiền đơn hàng',
            'Nhắc nhở thanh toán',
            'Đơn hàng hoàn thành'
        ];
        
        return $this->faker->randomElement($names);
    }

    /**
     * Get Vietnamese promotion template names
     */
    private function getVietnamesePromotionTemplateName(): string
    {
        $names = [
            'Khuyến mãi đặc biệt',
            'Flash Sale',
            'Sinh nhật khách hàng',
            'Ưu đãi thành viên VIP',
            'Khuyến mãi cuối tuần',
            'Sale mùa lễ hội'
        ];
        
        return $this->faker->randomElement($names);
    }

    /**
     * Get Vietnamese template tags
     */
    private function getVietnameseTemplateTags(): array
    {
        $tags = [
            'đơn hàng',
            'thanh toán',
            'khuyến mãi',
            'giao hàng',
            'khách hàng',
            'hệ thống',
            'sinh nhật',
            'VIP',
            'flash sale',
            'ưu đãi',
            'thông báo',
            'nhắc nhở',
            'xác nhận',
            'cảm ơn',
            'chào mừng'
        ];
        
        return $this->faker->randomElements($tags, $this->faker->numberBetween(2, 5));
    }

    /**
     * Get template metadata
     */
    private function getTemplateMetadata(): array
    {
        return [
            'version' => '1.0',
            'author' => $this->faker->name(),
            'created_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'last_modified' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'usage_count' => $this->faker->numberBetween(0, 1000),
            'success_rate' => $this->faker->randomFloat(2, 0.8, 1.0),
            'open_rate' => $this->faker->randomFloat(2, 0.2, 0.8),
            'click_rate' => $this->faker->randomFloat(2, 0.1, 0.5)
        ];
    }
}