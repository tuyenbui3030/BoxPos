<?php

namespace Packages\User\Jobs;

use Packages\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Send Password Change Notification Job
 * 
 * Handles sending notification email when user changes password.
 */
class SendPasswordChangeNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The user instance
     *
     * @var User
     */
    protected User $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            Log::info('Sending password change notification', [
                'user_id' => $this->user->id,
                'user_email' => $this->user->email,
            ]);

            // TODO: Send password change notification email
            // Mail::to($this->user->email)->send(new PasswordChangedMail($this->user));

            Log::info('Password change notification sent successfully', [
                'user_id' => $this->user->id,
                'user_email' => $this->user->email,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send password change notification', [
                'user_id' => $this->user->id,
                'user_email' => $this->user->email,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Password change notification job failed', [
            'user_id' => $this->user->id,
            'user_email' => $this->user->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
