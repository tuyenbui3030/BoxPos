<?php

namespace Packages\Notifications\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Notifications\Models\Notification;
use Packages\Notifications\Models\NotificationTemplate;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Notifications\Models\Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['email', 'sms', 'push', 'in_app']);
        $status = $this->faker->randomElement(['pending', 'sent', 'delivered', 'failed', 'read']);
        
        return [
            'store_id' => Store::factory(),
            'template_id' => \Packages\Notifications\Database\Factories\NotificationTemplateFactory::new(),
            'notification_number' => $this->faker->unique()->regexify('NOT[0-9]{8}'),
            'type' => $type,
            'category' => $this->faker->randomElement(['order', 'payment', 'promotion', 'system', 'customer', 'inventory']),
            'trigger' => $this->faker->randomElement(['manual', 'automatic', 'scheduled', 'event']),
            'priority' => $this->faker->randomElement(['low', 'normal', 'high', 'urgent']),
            'subject' => $this->getVietnameseSubject($type),
            'content' => $this->getVietnameseContent($type),
            'content_html' => $this->getVietnameseContent($type),
            'data' => $this->getNotificationData(),
            'recipient_type' => 'user',
            'recipient_id' => User::factory(),
            'recipient_email' => $this->faker->optional()->safeEmail(),
            'recipient_phone' => $this->faker->optional()->phoneNumber(),
            'recipient_name' => $this->faker->optional()->name(),
            'additional_recipients' => [],
            'related_type' => $this->faker->optional()->randomElement(['order', 'invoice', 'customer']),
            'related_id' => $this->faker->optional()->numberBetween(1, 1000),
            'status' => $status,
            'scheduled_at' => $this->faker->optional()->dateTimeBetween('now', '+1 week'),
            'sent_at' => $status !== 'pending' ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'delivered_at' => in_array($status, ['delivered', 'read']) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'read_at' => $status === 'read' ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'failed_at' => $status === 'failed' ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'failure_reason' => $status === 'failed' ? $this->getVietnameseFailureReason() : null,
            'retry_count' => $status === 'failed' ? $this->faker->numberBetween(1, 3) : 0,
            'max_retries' => 3,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create notification for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create notification for specific template
     */
    public function forTemplate($template): static
    {
        return $this->state(fn (array $attributes) => [
            'template_id' => is_object($template) ? $template->id : $template,
        ]);
    }

    /**
     * Create notification for specific recipient
     */
    public function forRecipient($recipient): static
    {
        return $this->state(fn (array $attributes) => [
            'recipient_id' => is_object($recipient) ? $recipient->id : $recipient,
        ]);
    }

    /**
     * Create email notification
     */
    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'email',
            'channel' => 'email',
            'recipient_email' => $this->faker->safeEmail(),
            'subject' => $this->getVietnameseEmailSubject(),
            'content' => $this->getVietnameseEmailContent(),
        ]);
    }

    /**
     * Create SMS notification
     */
    public function sms(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sms',
            'channel' => 'sms',
            'recipient_phone' => $this->generateVietnamesePhoneNumber(),
            'subject' => null,
            'content' => $this->getVietnameseSmsContent(),
        ]);
    }

    /**
     * Create push notification
     */
    public function push(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'push',
            'channel' => 'push',
            'subject' => $this->getVietnamesePushTitle(),
            'content' => $this->getVietnamesePushContent(),
        ]);
    }

    /**
     * Create in-app notification
     */
    public function inApp(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'in_app',
            'channel' => 'in_app',
            'subject' => $this->getVietnameseInAppTitle(),
            'content' => $this->getVietnameseInAppContent(),
        ]);
    }

    /**
     * Create pending notification
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'sent_at' => null,
            'delivered_at' => null,
            'read_at' => null,
            'failed_at' => null,
            'failure_reason' => null,
        ]);
    }

    /**
     * Create sent notification
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'sent_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'delivered_at' => null,
            'read_at' => null,
            'failed_at' => null,
            'failure_reason' => null,
        ]);
    }

    /**
     * Create delivered notification
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'sent_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'delivered_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'read_at' => null,
            'failed_at' => null,
            'failure_reason' => null,
        ]);
    }

    /**
     * Create read notification
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'read',
            'sent_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'delivered_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'read_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'failed_at' => null,
            'failure_reason' => null,
        ]);
    }

    /**
     * Create failed notification
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'sent_at' => null,
            'delivered_at' => null,
            'read_at' => null,
            'failed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'failure_reason' => $this->getVietnameseFailureReason(),
            'retry_count' => $this->faker->numberBetween(1, 3),
        ]);
    }

    /**
     * Create high priority notification
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    /**
     * Create urgent notification
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    /**
     * Create scheduled notification
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'scheduled_at' => $this->faker->dateTimeBetween('+1 hour', '+1 week'),
        ]);
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
            'Thông báo đơn hàng mới',
            'Xác nhận thanh toán',
            'Khuyến mãi đặc biệt',
            'Cập nhật trạng thái giao hàng',
            'Nhắc nhở quan trọng',
            'Chào mừng thành viên mới',
            'Thông báo hệ thống',
            'Ưu đãi sinh nhật',
            'Flash Sale đang diễn ra',
            'Đánh giá sản phẩm'
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
            'Xác nhận đơn hàng #12345 - BoxPos',
            'Cảm ơn bạn đã mua hàng tại BoxPos',
            'Khuyến mãi đặc biệt - Giảm 20% cho đơn hàng tiếp theo',
            'Thông báo giao hàng thành công',
            'Nhắc nhở thanh toán đơn hàng #12345',
            'Chào mừng bạn đến với BoxPos',
            'Thông báo hết hàng: Xi măng Portland',
            'Sinh nhật vui vẻ - Ưu đãi đặc biệt!',
            'Thông báo bảo trì hệ thống',
            'Mời bạn đánh giá đơn hàng #12345'
        ];
        
        return $this->faker->randomElement($subjects);
    }

    /**
     * Get Vietnamese email content
     */
    private function getVietnameseEmailContent(): string
    {
        $contents = [
            'Kính chào Anh/Chị,\n\nCảm ơn bạn đã đặt hàng tại BoxPos. Đơn hàng #12345 của bạn đã được xác nhận và đang được xử lý.\n\nThông tin đơn hàng:\n- Tổng tiền: 2.500.000đ\n- Ngày giao hàng dự kiến: 15/03/2024\n\nTrân trọng,\nBoxPos Team',
            'Xin chào,\n\nĐơn hàng #12345 của bạn đã được giao thành công.\n\nNếu có bất kỳ vấn đề gì, vui lòng liên hệ với chúng tôi qua số điện thoại 1900-1234 hoặc email support@boxpos.vn.\n\nCảm ơn bạn đã tin tưởng BoxPos!',
            'Chào bạn,\n\nNhân dịp sinh nhật của bạn, BoxPos xin gửi tặng mã giảm giá BIRTHDAY20 với ưu đãi 20%.\n\nMã có hiệu lực đến 31/03/2024.\n\nChúc bạn sinh nhật vui vẻ!'
        ];
        
        return $this->faker->randomElement($contents);
    }

    /**
     * Get Vietnamese SMS content
     */
    private function getVietnameseSmsContent(): string
    {
        $contents = [
            'BoxPos: Đơn hàng #12345 đã được xác nhận. Tổng tiền: 2.500.000đ. Cảm ơn bạn!',
            'BoxPos: Đơn hàng #12345 đã giao thành công. Cảm ơn bạn đã mua hàng!',
            'BoxPos: Khuyến mãi đặc biệt! Giảm 20% với mã SALE20. HSD: 31/03/2024',
            'BoxPos: Nhắc nhở thanh toán đơn hàng #12345. Tổng tiền: 2.500.000đ',
            'BoxPos: Chào mừng bạn! Tặng mã giảm giá WELCOME10 cho lần mua đầu tiên.'
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
            'Đơn hàng #12345 đã được xác nhận. Cảm ơn bạn!',
            'Đơn hàng #12345 đã giao thành công.',
            'Giảm 20% cho đơn hàng tiếp theo. Mã: SALE20',
            'Vui lòng thanh toán đơn hàng #12345',
            'Chào mừng bạn đến với BoxPos!',
            'Sinh nhật vui vẻ! Tặng ưu đãi 20%',
            'Sản phẩm mới Xi măng Portland đã có mặt',
            'Flash Sale - Giảm đến 50% chỉ trong hôm nay!',
            'Xi măng Portland sắp hết hàng. Đặt ngay!',
            'Đánh giá đơn hàng #12345 để nhận điểm thưởng'
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
            'Bạn có đơn hàng mới #12345 cần xử lý',
            'Thanh toán đơn hàng #12345 thành công',
            'Khuyến mãi 20% cho tất cả sản phẩm',
            'Hệ thống sẽ bảo trì vào 15/03/2024',
            'Đơn hàng #12345 đã được cập nhật trạng thái',
            'Ưu đãi đặc biệt dành riêng cho bạn',
            'Sản phẩm yêu thích Xi măng Portland đã có hàng',
            'Bạn có 500 điểm thưởng chưa sử dụng',
            'Đơn hàng #12345 đang được giao',
            'Mời bạn đánh giá đơn hàng #12345'
        ];
        
        return $this->faker->randomElement($contents);
    }

    /**
     * Get notification data
     */
    private function getNotificationData(): array
    {
        return [
            'order_id' => $this->faker->optional()->numberBetween(1, 1000),
            'customer_id' => $this->faker->optional()->numberBetween(1, 100),
            'product_id' => $this->faker->optional()->numberBetween(1, 200),
            'amount' => $this->faker->optional()->randomFloat(2, 100000, 5000000),
            'discount_code' => $this->faker->optional()->regexify('[A-Z]{4}[0-9]{2}'),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('+1 week', '+1 month')?->format('d/m/Y'),
            'action_url' => $this->faker->optional()->url(),
            'image_url' => $this->faker->optional()->imageUrl(),
        ];
    }

    /**
     * Get Vietnamese failure reasons
     */
    private function getVietnameseFailureReason(): string
    {
        $reasons = [
            'Email không hợp lệ',
            'Số điện thoại không tồn tại',
            'Lỗi kết nối mạng',
            'Máy chủ email không phản hồi',
            'Tin nhắn bị từ chối',
            'Thiết bị không nhận được thông báo',
            'Tài khoản email đã bị khóa',
            'Nội dung vi phạm chính sách',
            'Quá giới hạn gửi tin',
            'Lỗi hệ thống'
        ];
        
        return $this->faker->randomElement($reasons);
    }

    /**
     * Get notification metadata
     */
    private function getNotificationMetadata(): array
    {
        return [
            'user_agent' => $this->faker->optional()->userAgent(),
            'ip_address' => $this->faker->optional()->ipv4(),
            'device_type' => $this->faker->optional()->randomElement(['desktop', 'mobile', 'tablet']),
            'platform' => $this->faker->optional()->randomElement(['web', 'ios', 'android']),
            'app_version' => $this->faker->optional()->semver(),
            'tracking_id' => $this->faker->optional()->uuid(),
            'campaign_id' => $this->faker->optional()->numberBetween(1, 100),
            'source' => $this->faker->optional()->randomElement(['system', 'manual', 'scheduled', 'triggered']),
        ];
    }

    /**
     * Generate Vietnamese phone numbers
     */
    private function generateVietnamesePhoneNumber(): string
    {
        $prefixes = ['090', '091', '094', '083', '084', '085', '081', '082', '032', '033', '034', '035', '036', '037', '038', '039'];
        $prefix = $this->faker->randomElement($prefixes);
        $suffix = $this->faker->numerify('#######');
        
        return $prefix . $suffix;
    }
}