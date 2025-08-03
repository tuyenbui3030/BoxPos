<?php

namespace Packages\Notifications\Repositories;

use Packages\Notifications\Models\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationRepository
{
    protected Notification $model;

    public function __construct(Notification $model)
    {
        $this->model = $model;
    }

    /**
     * Get notifications for current user and store
     */
    public function getForUser(int $userId, int $storeId, int $limit = 10): Collection
    {
        return $this->model->query()
            ->where('store_id', $storeId)
            ->where('recipient_id', $userId)
            ->where('recipient_type', 'user')
            ->whereIn('status', ['sent', 'delivered'])
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount(int $userId, int $storeId): int
    {
        return $this->model->query()
            ->where('store_id', $storeId)
            ->where('recipient_id', $userId)
            ->where('recipient_type', 'user')
            ->whereIn('status', ['sent', 'delivered'])
            ->whereNull('read_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->count();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        return $this->model->query()
            ->where('id', $notificationId)
            ->where('recipient_id', $userId)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'is_read' => true,
            ]) > 0;
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(int $userId, int $storeId): int
    {
        return $this->model->query()
            ->where('store_id', $storeId)
            ->where('recipient_id', $userId)
            ->where('recipient_type', 'user')
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'is_read' => true,
            ]);
    }

    /**
     * Archive all notifications for user
     */
    public function archiveAll(int $userId, int $storeId): int
    {
        return $this->model->query()
            ->where('store_id', $storeId)
            ->where('recipient_id', $userId)
            ->where('recipient_type', 'user')
            ->update([
                'status' => 'archived',
                'archived_at' => now(),
            ]);
    }

    /**
     * Find notification by ID for user
     */
    public function findForUser(int $notificationId, int $userId): ?Notification
    {
        return $this->model->query()
            ->where('id', $notificationId)
            ->where('recipient_id', $userId)
            ->where('recipient_type', 'user')
            ->first();
    }
}