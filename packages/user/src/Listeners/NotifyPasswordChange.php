<?php

namespace Packages\User\Listeners;

use Packages\User\Events\UserPasswordChanged;
use Packages\User\Jobs\SendPasswordChangeNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Notify Password Change Listener
 * 
 * Handles notifications when a user changes their password.
 */
class NotifyPasswordChange implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param UserPasswordChanged $event
     * @return void
     */
    public function handle(UserPasswordChanged $event): void
    {
        $user = $event->user;

        // Log the password change
        Log::info('User password changed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'changed_at' => now()->toISOString(),
            'ip_address' => request()->ip(),
        ]);

        // Send password change notification email
        SendPasswordChangeNotificationJob::dispatch($user);
    }

    /**
     * Handle a job failure.
     *
     * @param UserPasswordChanged $event
     * @param \Throwable $exception
     * @return void
     */
    public function failed(UserPasswordChanged $event, \Throwable $exception): void
    {
        Log::error('Failed to send password change notification', [
            'user_id' => $event->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
