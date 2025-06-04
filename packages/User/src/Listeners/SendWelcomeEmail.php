<?php

namespace Packages\User\Listeners;

use Packages\User\Events\UserRegistered;
use Packages\User\Jobs\SendWelcomeEmailJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send Welcome Email Listener
 * 
 * Handles sending welcome email when a user registers.
 */
class SendWelcomeEmail implements ShouldQueue
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
     * @param UserRegistered $event
     * @return void
     */
    public function handle(UserRegistered $event): void
    {
        $user = $event->user;

        // Log the user registration
        Log::info('New user registered', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
        ]);

        // Dispatch welcome email job
        SendWelcomeEmailJob::dispatch($user);
    }

    /**
     * Handle a job failure.
     *
     * @param UserRegistered $event
     * @param \Throwable $exception
     * @return void
     */
    public function failed(UserRegistered $event, \Throwable $exception): void
    {
        Log::error('Failed to process user registration', [
            'user_id' => $event->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
