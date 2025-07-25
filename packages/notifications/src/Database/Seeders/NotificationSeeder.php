<?php

namespace Packages\Notifications\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Notifications\Models\NotificationTemplate;
use Packages\Notifications\Models\Notification;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔔 Seeding Notifications...');

        $stores = Store::all();
        
        foreach ($stores as $store) {
            // Create notification templates
            $templates = $this->createNotificationTemplates($store);
            
            // Create sample notifications for each template
            foreach ($templates as $template) {
                $this->createNotifications($template, rand(5, 15));
            }
        }

        $this->command->info('✅ Notifications seeded successfully!');
    }

    private function createNotificationTemplates(Store $store): \Illuminate\Support\Collection
    {
        $createdBy = $store->users()->first();
        
        $templates = [
            [
                'name' => 'Đơn hàng mới',
                'code' => 'NEW_ORDER',
                'type' => 'email',
                'category' => 'order',
                'trigger' => 'order_created',
                'subject' => 'Có đơn hàng mới #{order_number}',
                'content' => 'Đơn hàng #{order_number} từ khách hàng {customer_name} với giá trị {order_amount}',
                'placeholders' => json_encode(['order_number', 'customer_name', 'order_amount']),
            ],
            [
                'name' => 'Hàng sắp hết',
                'code' => 'LOW_STOCK',
                'type' => 'push',
                'category' => 'inventory',
                'trigger' => 'low_stock',
                'subject' => 'Cảnh báo hàng sắp hết: {material_name}',
                'content' => 'Vật liệu {material_name} chỉ còn {quantity} {unit} trong kho',
                'placeholders' => json_encode(['material_name', 'quantity', 'unit']),
            ],
            [
                'name' => 'Thanh toán thành công',
                'code' => 'PAYMENT_SUCCESS',
                'type' => 'email',
                'category' => 'payment',
                'trigger' => 'payment_received',
                'subject' => 'Thanh toán thành công #{payment_number}',
                'content' => 'Thanh toán {amount} cho đơn hàng #{order_number} đã được xử lý thành công',
                'placeholders' => json_encode(['payment_number', 'amount', 'order_number']),
            ],
            [
                'name' => 'Nhân viên chấm công muộn',
                'code' => 'LATE_CHECKIN',
                'type' => 'push',
                'category' => 'employee',
                'trigger' => 'employee_clock_in',
                'subject' => 'Nhân viên {employee_name} chấm công muộn',
                'content' => 'Nhân viên {employee_name} chấm công lúc {checkin_time}, muộn {late_minutes} phút',
                'placeholders' => json_encode(['employee_name', 'checkin_time', 'late_minutes']),
            ],
            [
                'name' => 'Khuyến mãi mới',
                'code' => 'NEW_PROMOTION',
                'type' => 'email',
                'category' => 'marketing',
                'trigger' => 'promotion_started',
                'subject' => 'Khuyến mãi mới: {promotion_name}',
                'content' => 'Chương trình khuyến mãi {promotion_name} với ưu đãi {discount_value}',
                'placeholders' => json_encode(['promotion_name', 'discount_value']),
            ],
        ];

        $createdTemplates = collect();
        
        foreach ($templates as $templateData) {
            $template = NotificationTemplate::create(array_merge($templateData, [
                'store_id' => $store->id,
                'is_active' => true,
                'priority' => rand(1, 10),
                'created_by' => $createdBy?->id,
            ]));
            
            $createdTemplates->push($template);
        }

        return $createdTemplates;
    }

    private function createNotifications(NotificationTemplate $template, int $count): void
    {
        $store = Store::find($template->store_id);
        $users = $store->users()->limit(10)->get();
        
        for ($i = 0; $i < $count; $i++) {
            $user = $users->random();
            $createdAt = Carbon::now()->subDays(rand(1, 30));
            $data = $this->generateNotificationData($template);
            
            $this->createNotification($template, $user, $data, $createdAt);
        }
    }

    private function createNotification(NotificationTemplate $template, User $user, array $data, Carbon $createdAt): void
    {
        Notification::create([
            'store_id' => $template->store_id,
            'template_id' => $template->id,
            'notification_number' => 'NOT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'type' => $template->type,
            'category' => $template->category,
            'trigger' => $template->trigger,
            'priority' => $this->getRandomPriority(),
            'subject' => $this->processTemplate($template->subject, $data),
            'content' => $this->processTemplate($template->content, $data),
            'data' => json_encode($data),
            'recipient_type' => 'user',
            'recipient_id' => $user->id,
            'recipient_email' => $user->email,
            'recipient_name' => $user->name,
            'status' => $this->getRandomStatus($createdAt),
            'scheduled_at' => $createdAt,
            'sent_at' => $this->getSentAt($createdAt),
            'delivered_at' => $this->getDeliveredAt($createdAt),
            'read_at' => $this->getReadAt($createdAt),
            'metadata' => json_encode([
                'created_via' => 'seeder',
                'device_type' => $this->getRandomDeviceType(),
            ]),
        ]);
    }

    private function generateNotificationData(NotificationTemplate $template): array
    {
        return match($template->code) {
            'NEW_ORDER' => [
                'order_number' => 'SO' . str_pad(rand(1, 9999), 6, '0', STR_PAD_LEFT),
                'customer_name' => 'Nguyễn Văn A',
                'order_amount' => number_format(rand(500000, 5000000)) . ' VND',
            ],
            'LOW_STOCK' => [
                'material_name' => 'Xi măng Portland',
                'quantity' => rand(1, 10),
                'unit' => 'bao',
            ],
            'PAYMENT_SUCCESS' => [
                'payment_number' => 'PAY' . str_pad(rand(1, 9999), 6, '0', STR_PAD_LEFT),
                'amount' => number_format(rand(500000, 5000000)) . ' VND',
                'order_number' => 'SO' . str_pad(rand(1, 9999), 6, '0', STR_PAD_LEFT),
            ],
            'LATE_CHECKIN' => [
                'employee_name' => 'Trần Thị B',
                'checkin_time' => '08:' . str_pad(rand(15, 45), 2, '0', STR_PAD_LEFT),
                'late_minutes' => rand(15, 45),
            ],
            'NEW_PROMOTION' => [
                'promotion_name' => 'Khuyến mãi cuối năm',
                'discount_value' => rand(10, 50) . '%',
            ],
            default => [
                'message' => 'Thông báo từ hệ thống',
                'timestamp' => now()->format('d/m/Y H:i'),
            ],
        };
    }

    private function processTemplate(string $template, array $data): string
    {
        $processed = $template;

        foreach ($data as $key => $value) {
            $processed = str_replace('{' . $key . '}', $value, $processed);
        }

        return $processed;
    }

    private function getRandomPriority(): int
    {
        $priorities = [1, 5, 8, 10]; // low, normal, high, urgent
        $weights = [20, 50, 25, 5];

        $random = rand(1, 100);
        $cumulative = 0;

        foreach ($priorities as $index => $priority) {
            $cumulative += $weights[$index];
            if ($random <= $cumulative) {
                return $priority;
            }
        }

        return 5; // normal
    }

    private function getRandomStatus(Carbon $createdAt): string
    {
        $statuses = ['sent', 'delivered', 'delivered', 'failed'];
        $weights = [10, 30, 50, 10]; // 10% sent, 30% delivered, 50% delivered, 10% failed

        $random = rand(1, 100);
        $cumulative = 0;

        foreach ($statuses as $index => $status) {
            $cumulative += $weights[$index];
            if ($random <= $cumulative) {
                return $status;
            }
        }

        return 'sent';
    }

    private function getSentAt(Carbon $createdAt): ?Carbon
    {
        return $createdAt->copy()->addMinutes(rand(1, 60));
    }

    private function getDeliveredAt(Carbon $createdAt): ?Carbon
    {
        return $createdAt->copy()->addMinutes(rand(2, 65));
    }

    private function getReadAt(Carbon $createdAt): ?Carbon
    {
        // 60% chance of being read
        if (rand(1, 100) <= 60) {
            return $createdAt->copy()->addHours(rand(1, 48));
        }
        return null;
    }

    private function getRandomDeviceType(): string
    {
        $devices = ['web', 'mobile', 'tablet', 'desktop'];
        return $devices[array_rand($devices)];
    }
}
