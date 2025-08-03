<?php

namespace Packages\Notifications\Services;

use Packages\Notifications\Repositories\NotificationRepository;
use Packages\Notifications\Models\Notification;
use Packages\Log\Traits\Loggable;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    use Loggable;

    protected NotificationRepository $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Get notifications for current user
     */
    public function getNotificationsForUser(int $userId, int $storeId, int $limit = 10): Collection
    {
        $this->logActivity('notifications_fetched', [
            'user_id' => $userId,
            'store_id' => $storeId,
            'limit' => $limit,
        ]);

        return $this->notificationRepository->getForUser($userId, $storeId, $limit);
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount(int $userId, int $storeId): int
    {
        return $this->notificationRepository->getUnreadCount($userId, $storeId);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $this->logActivity('notification_marked_as_read', [
            'notification_id' => $notificationId,
            'user_id' => $userId,
        ]);

        return $this->notificationRepository->markAsRead($notificationId, $userId);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(int $userId, int $storeId): int
    {
        $count = $this->notificationRepository->markAllAsRead($userId, $storeId);

        $this->logActivity('all_notifications_marked_as_read', [
            'user_id' => $userId,
            'store_id' => $storeId,
            'count' => $count,
        ]);

        return $count;
    }

    /**
     * Archive all notifications
     */
    public function archiveAll(int $userId, int $storeId): int
    {
        $count = $this->notificationRepository->archiveAll($userId, $storeId);

        $this->logActivity('all_notifications_archived', [
            'user_id' => $userId,
            'store_id' => $storeId,
            'count' => $count,
        ]);

        return $count;
    }

    /**
     * Format notifications for display
     */
    public function formatNotificationsForDisplay(Collection $notifications): array
    {
        return $notifications->map(function (Notification $notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->subject,
                'message' => $notification->content,
                'type' => $notification->type,
                'category' => $notification->category,
                'priority' => $notification->priority,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
                'action_url' => $notification->action_url,
                'action_text' => $notification->action_text,
                'color' => $this->getColorByCategory($notification->category),
                'icon' => $this->getIconByCategory($notification->category),
            ];
        })->toArray();
    }

    /**
     * Get color by category
     */
    private function getColorByCategory(string $category): string
    {
        return match($category) {
            'security' => 'red',
            'system' => 'blue',
            'order' => 'green',
            'payment' => 'success',
            'inventory' => 'warning',
            'employee' => 'info',
            'marketing' => 'purple',
            'customer' => 'teal',
            default => 'gray'
        };
    }

    /**
     * Get icon by category
     */
    private function getIconByCategory(string $category): string
    {
        return match($category) {
            'security' => 'shield-alert',
            'system' => 'settings',
            'order' => 'shopping-cart',
            'payment' => 'credit-card',
            'inventory' => 'package',
            'employee' => 'users',
            'marketing' => 'megaphone',
            'customer' => 'user-plus',
            default => 'bell'
        };
    }
}