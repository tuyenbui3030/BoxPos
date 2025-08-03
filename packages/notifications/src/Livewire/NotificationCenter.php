<?php

namespace Packages\Notifications\Livewire;

use Livewire\Component;
use Packages\Notifications\Services\NotificationService;
use Packages\Log\Traits\Loggable;

class NotificationCenter extends Component
{
    use Loggable;

    public $unreadCount = 0;
    public $notifications = [];
    public $showDropdown = false;

    protected NotificationService $notificationService;

    protected $listeners = [
        'notification-received' => 'handleNewNotification',
        'notification-read' => 'handleNotificationRead'
    ];

    public function boot(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function mount()
    {
        $this->logActivity('notification_center_mounted');
        $this->loadNotifications();
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
        
        if ($this->showDropdown) {
            $this->loadNotifications();
            $this->logActivity('notification_dropdown_opened');
        } else {
            $this->logActivity('notification_dropdown_closed');
        }
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
        $this->logActivity('notification_dropdown_closed');
    }

    public function markAsRead($notificationId)
    {
        if (!auth()->check()) {
            return;
        }

        try {
            $success = $this->notificationService->markAsRead(
                $notificationId, 
                auth()->id()
            );

            if ($success) {
                $this->loadNotifications();
                $this->dispatch('notification-read', $notificationId);
            }
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'mark_notification_as_read',
                'notification_id' => $notificationId
            ]);
        }
    }

    public function markAllAsRead()
    {
        if (!auth()->check() || !auth()->user()->currentStore) {
            return;
        }

        try {
            $count = $this->notificationService->markAllAsRead(
                auth()->id(),
                auth()->user()->currentStore->id
            );

            $this->loadNotifications();
            $this->dispatch('all-notifications-read');

            session()->flash('success', "Đã đánh dấu {$count} thông báo là đã đọc");
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'mark_all_notifications_as_read'
            ]);
        }
    }

    public function archiveAll()
    {
        if (!auth()->check() || !auth()->user()->currentStore) {
            return;
        }

        try {
            $count = $this->notificationService->archiveAll(
                auth()->id(),
                auth()->user()->currentStore->id
            );

            $this->loadNotifications();
            $this->dispatch('all-notifications-archived');

            session()->flash('success', "Đã lưu trữ {$count} thông báo");
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'archive_all_notifications'
            ]);
        }
    }

    public function handleNewNotification($notification)
    {
        $this->logActivity('new_notification_received', [
            'notification_type' => $notification['type'] ?? 'unknown'
        ]);
        
        $this->loadNotifications();
    }

    public function handleNotificationRead($notificationId)
    {
        $this->loadNotifications();
    }

    protected function loadNotifications()
    {
        if (!auth()->check() || !auth()->user()->currentStore) {
            $this->notifications = [];
            $this->unreadCount = 0;
            return;
        }

        try {
            $user = auth()->user();
            $store = $user->currentStore;

            // Get notifications
            $rawNotifications = $this->notificationService->getNotificationsForUser(
                $user->id,
                $store->id,
                10
            );

            // Format for display
            $this->notifications = $this->notificationService->formatNotificationsForDisplay($rawNotifications);

            // Get unread count
            $this->unreadCount = $this->notificationService->getUnreadCount(
                $user->id,
                $store->id
            );
            
            $this->logActivity('notifications_loaded', [
                'total_notifications' => count($this->notifications),
                'unread_count' => $this->unreadCount
            ]);
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'load_notifications'
            ]);
            
            $this->notifications = [];
            $this->unreadCount = 0;
        }
    }

    public function render()
    {
        return view('notifications::livewire.notification-center');
    }
}