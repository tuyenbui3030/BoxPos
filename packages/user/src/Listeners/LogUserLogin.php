<?php

namespace Packages\User\Listeners;

use Packages\User\Events\UserLoggedIn;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log User Login Listener
 * 
 * Handles logging when a user logs in.
 */
class LogUserLogin
{
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
     * @param UserLoggedIn $event
     * @return void
     */
    public function handle(UserLoggedIn $event): void
    {
        $user = $event->user;

        // Log the user login
        Log::info('User logged in', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'login_time' => $event->loginTime->toISOString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Update last login timestamp if the field exists
        if ($user->hasAttribute('last_login_at')) {
            $user->update(['last_login_at' => $event->loginTime]);
        }

        // TODO: Add session tracking or device tracking logic here
        // DeviceTracker::recordLogin($user, request());
    }
}
