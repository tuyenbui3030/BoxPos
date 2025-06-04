<?php

namespace Packages\Customer\Listeners;

use Packages\Customer\Events\CustomerCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send Welcome Email Listener
 * 
 * Handles sending welcome email when a customer is created.
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
     * @param CustomerCreated $event
     * @return void
     */
    public function handle(CustomerCreated $event): void
    {
        $customer = $event->customer;

        // Log the customer creation
        Log::info('New customer created', [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
        ]);

        // TODO: Send welcome email
        // Mail::to($customer->email)->send(new WelcomeCustomerMail($customer));
    }

    /**
     * Handle a job failure.
     *
     * @param CustomerCreated $event
     * @param \Throwable $exception
     * @return void
     */
    public function failed(CustomerCreated $event, \Throwable $exception): void
    {
        Log::error('Failed to send welcome email to customer', [
            'customer_id' => $event->customer->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
