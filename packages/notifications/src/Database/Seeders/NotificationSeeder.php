<?php

namespace Packages\Notifications\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Packages\Notifications\Models\NotificationTemplate;
use Packages\Notifications\Models\Notification;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Carbon\Carbon;

class NotificationSeeder extends BasePackageSeeder
{
    public function run(): void
    {
        $this->ensureSeedingAllowed();
        
        $this->executeWithTransaction(function () {
            $this->logSeedingProgress('notifications_seeding_started');

            $this->seedForAllStores(function (Store $store) {
                // Create notification templates
                $templates = $this->createNotificationTemplates($store);
                
                // Create sample notifications for each template
                foreach ($templates as $template) {
                    $notificationCount = $this->getRecordCount(15, 3);
                    $this->createNotifications($template, $notificationCount);
                }
            });

            $this->logSeedingProgress('notifications_seeding_completed');
        });
    }

    private function createNotificationTemplates(Store $store): \Illuminate\Support\Collection
    {
        $this->logSeedingProgress('creating_notification_templates', [
            'store_id' => $store->id,
            'store_name' => $store->name
        ]);

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
            [
                'name' => 'Báo cáo hoàn thành',
                'code' => 'REPORT_COMPLETED',
                'type' => 'push',
                'category' => 'system',
                'trigger' => 'data_export_ready', // Using valid enum value
                'subject' => 'Báo cáo {report_name} đã hoàn thành',
                'content' => 'Báo cáo {report_name} cho kỳ {period} đã được tạo thành công. Bạn có thể xem tại {report_url}',
                'placeholders' => json_encode(['report_name', 'period', 'report_url']),
            ],
            [
                'name' => 'Cảnh báo bảo mật',
                'code' => 'SECURITY_ALERT',
                'type' => 'email',
                'category' => 'alert',
                'trigger' => 'security_alert', // Using valid enum value
                'subject' => 'Cảnh báo bảo mật: {alert_type}',
                'content' => 'Phát hiện hoạt động bất thường: {alert_description} vào lúc {timestamp}',
                'placeholders' => json_encode(['alert_type', 'alert_description', 'timestamp']),
            ],
            [
                'name' => 'Nhắc nhở công việc',
                'code' => 'TASK_REMINDER',
                'type' => 'push',
                'category' => 'reminder',
                'trigger' => 'appointment_reminder', // Using valid enum value
                'subject' => 'Nhắc nhở: {task_name} sắp đến hạn',
                'content' => 'Công việc {task_name} sẽ đến hạn vào {due_date}. Vui lòng hoàn thành sớm.',
                'placeholders' => json_encode(['task_name', 'due_date']),
            ],
            [
                'name' => 'Khách hàng mới',
                'code' => 'NEW_CUSTOMER',
                'type' => 'email',
                'category' => 'customer',
                'trigger' => 'customer_registered',
                'subject' => 'Khách hàng mới: {customer_name}',
                'content' => 'Khách hàng {customer_name} vừa đăng ký tài khoản với email {customer_email}',
                'placeholders' => json_encode(['customer_name', 'customer_email']),
            ],
        ];

        $createdTemplates = collect();
        
        foreach ($templates as $templateData) {
            $template = NotificationTemplate::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'code' => $templateData['code']
                ],
                array_merge($templateData, [
                'store_id' => $store->id,
                'is_active' => true,
                'is_system' => true, // Mark as system templates
                'priority' => $this->getTemplatePriority($templateData['category']),
                'delay_minutes' => $this->getDelayMinutes($templateData['category']),
                'send_to_admin' => $this->shouldSendToAdmin($templateData['category']),
                'send_to_customer' => $this->shouldSendToCustomer($templateData['category']),
                'admin_roles' => json_encode($this->getAdminRoles($templateData['category'])),
                'respect_quiet_hours' => $templateData['category'] !== 'security',
                'quiet_hours_start' => '22:00:00',
                'quiet_hours_end' => '07:00:00',
                'email_settings' => $templateData['type'] === 'email' ? json_encode([
                    'from_name' => 'BoxPos System',
                    'reply_to' => 'noreply@boxpos.vn',
                    'template_style' => 'default'
                ]) : null,
                'push_settings' => $templateData['type'] === 'push' ? json_encode([
                    'sound' => 'default',
                    'badge' => true,
                    'collapse_key' => $templateData['category']
                ]) : null,
                'conditions' => json_encode([
                    'user_preferences' => true,
                    'business_hours_only' => $templateData['category'] === 'marketing',
                    'min_priority' => 1,
                ]),
                'require_opt_in' => $templateData['category'] === 'marketing',
                'created_by' => $createdBy?->id,
                'metadata' => json_encode([
                    'created_via' => 'seeder',
                    'version' => '1.0',
                    'system_template' => true,
                    'auto_generated' => true,
                ]),
            ]));
            
            $createdTemplates->push($template);
            
            $this->logSeedingProgress('notification_template_created', [
                'template_id' => $template->id,
                'template_code' => $template->code,
                'store_id' => $store->id
            ]);
        }

        return $createdTemplates;
    }

    private function createNotifications(NotificationTemplate $template, int $count): void
    {
        $this->logSeedingProgress('creating_notifications', [
            'template_id' => $template->id,
            'template_code' => $template->code,
            'notification_count' => $count
        ]);

        $store = Store::find($template->store_id);
        $users = $store->users()->limit(10)->get();
        
        if ($users->isEmpty()) {
            $this->logSeedingProgress('no_users_found_for_notifications', [
                'store_id' => $store->id,
                'template_id' => $template->id
            ]);
            return;
        }
        
        for ($i = 0; $i < $count; $i++) {
            $user = $users->random();
            $createdAt = Carbon::now()->subDays(rand(1, 30));
            $data = $this->generateNotificationData($template);
            
            $this->createNotification($template, $user, $data, $createdAt);
        }

        if ($count > 0) {
            $this->logSeedingProgress('notifications_created', [
                'template_id' => $template->id,
                'count' => $count
            ]);
        }
    }

    private function createNotification(NotificationTemplate $template, User $user, array $data, Carbon $createdAt): void
    {
        $status = $this->getRandomStatus($createdAt);
        $sentAt = $this->getSentAt($createdAt, $status);
        $deliveredAt = $this->getDeliveredAt($sentAt, $status);
        $readAt = $this->getReadAt($deliveredAt, $status);

        Notification::create([
            'store_id' => $template->store_id,
            'template_id' => $template->id,
            'notification_number' => 'NOT-' . $createdAt->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'type' => $template->type,
            'category' => $template->category,
            'trigger' => $template->trigger,
            'priority' => max(1, min(10, $template->priority + rand(-2, 2))), // Slight variation from template, keep in range 1-10
            'subject' => $this->processTemplate($template->subject, $data),
            'content' => $this->processTemplate($template->content, $data),
            'content_html' => $this->generateHtmlContent($template, $data),
            'data' => json_encode($data),
            'recipient_type' => 'user',
            'recipient_id' => $user->id,
            'recipient_email' => $user->email,
            'recipient_name' => $user->name,
            'related_type' => $this->getRelatedType($template->category),
            'related_id' => $this->getRelatedId($template->category),
            'related_data' => json_encode($this->getRelatedData($template->category, $data)),
            'scheduled_at' => $createdAt,
            'sent_at' => $sentAt,
            'delivered_at' => $deliveredAt,
            'read_at' => $readAt,
            'expires_at' => $createdAt->copy()->addDays($this->getExpirationDays($template->category)),
            'status' => $status,
            'failure_reason' => $status === 'failed' ? $this->getRandomErrorMessage() : null,
            'retry_count' => $status === 'failed' ? rand(1, 3) : 0,
            'next_retry_at' => $status === 'failed' ? $createdAt->copy()->addMinutes(rand(30, 120)) : null,
            'max_retries' => 3,
            'provider' => $this->getProvider($template->type),
            'provider_message_id' => $status !== 'failed' ? 'msg_' . rand(100000, 999999) : null,
            'provider_response' => $status !== 'failed' ? json_encode(['status' => 'success', 'message_id' => 'msg_' . rand(100000, 999999)]) : null,
            'cost' => $this->getCost($template->type),
            'currency' => 'VND',
            'is_read' => $readAt !== null,
            'is_clicked' => $readAt !== null && rand(0, 1) === 1,
            'click_count' => $readAt !== null && rand(0, 1) === 1 ? rand(1, 3) : 0,
            'first_clicked_at' => $readAt !== null && rand(0, 1) === 1 ? $readAt->copy()->addMinutes(rand(1, 30)) : null,
            'can_unsubscribe' => $template->category === 'marketing',
            'is_unsubscribed' => false,
            'tracking_data' => json_encode([
                'user_agent' => $this->getRandomUserAgent(),
                'ip_address' => $this->getRandomIpAddress(),
                'device_type' => $this->getRandomDeviceType(),
            ]),
            'metadata' => json_encode([
                'created_via' => 'seeder',
                'template_version' => '1.0',
                'batch_id' => 'batch_' . date('Ymd_His'),
            ]),
        ]);
    }

    private function generateNotificationData(NotificationTemplate $template): array
    {
        $vietnameseNames = $this->getVietnameseNames();
        
        return match($template->code) {
            'NEW_ORDER' => [
                'order_number' => 'SO' . str_pad(rand(1, 9999), 6, '0', STR_PAD_LEFT),
                'customer_name' => $vietnameseNames[array_rand($vietnameseNames)],
                'order_amount' => number_format(rand(500000, 5000000)) . ' VND',
            ],
            'LOW_STOCK' => [
                'material_name' => $this->getRandomMaterialName(),
                'quantity' => rand(1, 10),
                'unit' => $this->getRandomUnit(),
            ],
            'PAYMENT_SUCCESS' => [
                'payment_number' => 'PAY' . str_pad(rand(1, 9999), 6, '0', STR_PAD_LEFT),
                'amount' => number_format(rand(500000, 5000000)) . ' VND',
                'order_number' => 'SO' . str_pad(rand(1, 9999), 6, '0', STR_PAD_LEFT),
            ],
            'LATE_CHECKIN' => [
                'employee_name' => $vietnameseNames[array_rand($vietnameseNames)],
                'checkin_time' => '08:' . str_pad(rand(15, 45), 2, '0', STR_PAD_LEFT),
                'late_minutes' => rand(15, 45),
            ],
            'NEW_PROMOTION' => [
                'promotion_name' => $this->getRandomPromotionName(),
                'discount_value' => rand(10, 50) . '%',
            ],
            'REPORT_COMPLETED' => [
                'report_name' => $this->getRandomReportName(),
                'period' => $this->getRandomPeriod(),
                'report_url' => '/reports/' . rand(1000, 9999),
            ],
            'SECURITY_ALERT' => [
                'alert_type' => $this->getRandomSecurityAlert(),
                'alert_description' => 'Đăng nhập từ địa chỉ IP lạ',
                'timestamp' => now()->format('d/m/Y H:i:s'),
            ],
            'TASK_REMINDER' => [
                'task_name' => $this->getRandomTaskName(),
                'due_date' => now()->addDays(rand(1, 7))->format('d/m/Y'),
            ],
            'NEW_CUSTOMER' => [
                'customer_name' => $vietnameseNames[array_rand($vietnameseNames)],
                'customer_email' => strtolower(str_replace(' ', '.', $vietnameseNames[array_rand($vietnameseNames)])) . '@example.com',
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
        // Valid statuses: 'pending', 'scheduled', 'sending', 'sent', 'delivered', 'failed', 'cancelled', 'expired'
        $statuses = ['sent', 'delivered', 'delivered', 'failed'];
        $weights = [15, 35, 40, 10]; // 15% sent, 35% delivered, 40% delivered (read handled by is_read flag), 10% failed

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

    private function getSentAt(Carbon $createdAt, string $status): ?Carbon
    {
        if (in_array($status, ['sent', 'delivered', 'read'])) {
            return $createdAt->copy()->addMinutes(rand(1, 60));
        }
        return $status === 'failed' ? $createdAt->copy()->addMinutes(rand(1, 30)) : null;
    }

    private function getDeliveredAt(?Carbon $sentAt, string $status): ?Carbon
    {
        if (in_array($status, ['delivered', 'read']) && $sentAt) {
            return $sentAt->copy()->addMinutes(rand(1, 30));
        }
        return null;
    }

    private function getReadAt(?Carbon $deliveredAt, string $status): ?Carbon
    {
        // 60% chance of being read if delivered
        if ($status === 'delivered' && $deliveredAt && rand(1, 100) <= 60) {
            return $deliveredAt->copy()->addHours(rand(1, 48));
        }
        return null;
    }

    private function getRandomDeviceType(): string
    {
        $devices = ['web', 'mobile', 'tablet', 'desktop'];
        return $devices[array_rand($devices)];
    }

    private function getTemplatePriority(string $category): int
    {
        return match($category) {
            'security' => 10, // Urgent
            'system' => 8,    // High
            'order', 'payment' => 7, // High
            'inventory', 'employee' => 5, // Normal
            'marketing', 'customer' => 3, // Low
            'task' => 4, // Normal-Low
            default => 5
        };
    }

    private function getDelayMinutes(string $category): int
    {
        return match($category) {
            'security' => 0,     // Immediate
            'system' => 0,       // Immediate
            'order', 'payment' => 1, // 1 minute delay
            'inventory' => 5,    // 5 minutes delay
            'marketing' => 60,   // 1 hour delay
            'employee' => 2,     // 2 minutes delay
            default => 0
        };
    }

    private function shouldSendToAdmin(string $category): bool
    {
        return in_array($category, ['security', 'system', 'inventory', 'employee', 'order']);
    }

    private function shouldSendToCustomer(string $category): bool
    {
        return in_array($category, ['order', 'payment', 'marketing', 'customer']);
    }

    private function getAdminRoles(string $category): array
    {
        return match($category) {
            'security' => ['admin', 'manager'],
            'system' => ['admin'],
            'order', 'payment' => ['admin', 'manager', 'sales'],
            'inventory' => ['admin', 'manager', 'warehouse'],
            'employee' => ['admin', 'manager', 'hr'],
            'marketing' => ['admin', 'manager', 'marketing'],
            default => ['admin']
        };
    }

    private function getRandomMaterialName(): string
    {
        $materials = [
            'Xi măng Portland', 'Sắt thép D10', 'Gạch đỏ', 'Cát vàng',
            'Đá dăm', 'Thép hình', 'Gạch block', 'Cát bê tông',
            'Xi măng trắng', 'Sắt V6', 'Gạch ống', 'Đá mi'
        ];
        return $materials[array_rand($materials)];
    }

    private function getRandomUnit(): string
    {
        $units = ['bao', 'kg', 'm3', 'cái', 'tấn', 'm2', 'thanh', 'viên'];
        return $units[array_rand($units)];
    }

    private function getRandomPromotionName(): string
    {
        $promotions = [
            'Khuyến mãi cuối năm', 'Giảm giá mùa hè', 'Ưu đãi khách hàng VIP',
            'Sale tháng 3', 'Khuyến mãi khai trương', 'Giảm giá Black Friday',
            'Ưu đãi sinh nhật', 'Sale cuối tuần'
        ];
        return $promotions[array_rand($promotions)];
    }

    private function getRandomReportName(): string
    {
        $reports = [
            'Báo cáo doanh thu', 'Báo cáo tồn kho', 'Báo cáo khách hàng',
            'Báo cáo nhân viên', 'Báo cáo tài chính', 'Báo cáo bán hàng'
        ];
        return $reports[array_rand($reports)];
    }

    private function getRandomPeriod(): string
    {
        $periods = [
            'Tháng ' . rand(1, 12) . '/2024',
            'Quý ' . rand(1, 4) . '/2024',
            'Tuần ' . rand(1, 52) . '/2024',
            'Ngày ' . rand(1, 28) . '/' . rand(1, 12) . '/2024'
        ];
        return $periods[array_rand($periods)];
    }

    private function getRandomSecurityAlert(): string
    {
        $alerts = [
            'Đăng nhập bất thường', 'Thay đổi mật khẩu', 'Truy cập từ thiết bị mới',
            'Nhiều lần đăng nhập thất bại', 'Thay đổi thông tin tài khoản'
        ];
        return $alerts[array_rand($alerts)];
    }

    private function getRandomTaskName(): string
    {
        $tasks = [
            'Kiểm tra tồn kho', 'Cập nhật giá bán', 'Liên hệ nhà cung cấp',
            'Xử lý đơn hàng', 'Chấm công nhân viên', 'Báo cáo doanh thu'
        ];
        return $tasks[array_rand($tasks)];
    }

    private function generateHtmlContent(NotificationTemplate $template, array $data): ?string
    {
        if ($template->type !== 'email') {
            return null;
        }

        $content = $this->processTemplate($template->content, $data);
        return "<div style='font-family: Arial, sans-serif; padding: 20px;'>" .
               "<h3 style='color: #333;'>" . $this->processTemplate($template->subject, $data) . "</h3>" .
               "<p style='color: #666; line-height: 1.6;'>" . nl2br($content) . "</p>" .
               "</div>";
    }

    private function getRelatedType(string $category): ?string
    {
        return match($category) {
            'order' => 'sales_order',
            'payment' => 'payment',
            'inventory' => 'material_inventory',
            'customer' => 'customer',
            'employee' => 'employee',
            'marketing' => 'promotion',
            default => null
        };
    }

    private function getRelatedId(string $category): ?int
    {
        return $category !== 'system' ? rand(1, 100) : null;
    }

    private function getRandomErrorMessage(): string
    {
        $errors = [
            'Không thể gửi email: SMTP server không phản hồi',
            'Push notification thất bại: Device token không hợp lệ',
            'SMS gửi thất bại: Số điện thoại không tồn tại',
            'Lỗi kết nối mạng khi gửi thông báo',
            'Rate limit exceeded: Quá nhiều thông báo trong thời gian ngắn'
        ];
        return $errors[array_rand($errors)];
    }

    private function generateDeliveryAttempts(string $status): array
    {
        $attempts = [];
        $attemptCount = $status === 'failed' ? rand(2, 4) : 1;

        for ($i = 1; $i <= $attemptCount; $i++) {
            $attempts[] = [
                'attempt' => $i,
                'timestamp' => now()->subMinutes(rand(1, 60 * $i))->toISOString(),
                'status' => $i === $attemptCount ? $status : ($i < $attemptCount && $status === 'failed' ? 'failed' : 'sent'),
                'response_time' => rand(100, 2000) . 'ms'
            ];
        }

        return $attempts;
    }

    private function getActionUrl(string $category, array $data): ?string
    {
        return match($category) {
            'order' => '/orders/' . ($data['order_number'] ?? rand(1000, 9999)),
            'payment' => '/payments/' . ($data['payment_number'] ?? rand(1000, 9999)),
            'inventory' => '/inventory',
            'customer' => '/customers',
            'employee' => '/employees',
            'marketing' => '/promotions',
            'system' => '/reports',
            default => null
        };
    }

    private function getActionText(string $category): ?string
    {
        return match($category) {
            'order' => 'Xem đơn hàng',
            'payment' => 'Xem thanh toán',
            'inventory' => 'Kiểm tra kho',
            'customer' => 'Xem khách hàng',
            'employee' => 'Xem nhân viên',
            'marketing' => 'Xem khuyến mãi',
            'system' => 'Xem báo cáo',
            default => null
        };
    }

    private function getExpirationDays(string $category): int
    {
        return match($category) {
            'security' => 1,     // 1 day
            'system' => 7,       // 1 week
            'order', 'payment' => 30, // 1 month
            'marketing' => 14,   // 2 weeks
            default => 7         // 1 week
        };
    }

    private function getRandomUserAgent(): string
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15',
            'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0'
        ];
        return $userAgents[array_rand($userAgents)];
    }

    private function getRandomIpAddress(): string
    {
        return rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255);
    }

    private function getRelatedData(string $category, array $data): array
    {
        return match($category) {
            'order' => [
                'order_number' => $data['order_number'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'order_amount' => $data['order_amount'] ?? null,
            ],
            'payment' => [
                'payment_number' => $data['payment_number'] ?? null,
                'amount' => $data['amount'] ?? null,
            ],
            'inventory' => [
                'material_name' => $data['material_name'] ?? null,
                'quantity' => $data['quantity'] ?? null,
            ],
            default => $data
        };
    }

    private function getProvider(string $type): ?string
    {
        return match($type) {
            'email' => 'smtp',
            'sms' => 'twilio',
            'push' => 'firebase',
            default => null
        };
    }

    private function getCost(string $type): float
    {
        return match($type) {
            'email' => 0.0001, // Very small cost for email
            'sms' => 0.05,     // 50 VND per SMS
            'push' => 0.0,     // Free push notifications
            default => 0.0
        };
    }
}
