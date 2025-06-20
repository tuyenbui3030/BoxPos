<?php

namespace Packages\Customer\Listeners;

use Packages\Customer\Events\CustomerUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log Customer Update Listener
 * 
 * Handles logging when a customer is updated.
 */
class LogCustomerUpdate
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
     * @param CustomerUpdated $event
     * @return void
     */
    public function handle(CustomerUpdated $event): void
    {
        $customer = $event->customer;

        // Log the customer update
        Log::info('Customer updated', [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'updated_at' => $customer->updated_at->toISOString(),
        ]);

        // TODO: Add audit trail logic here if needed
        // AuditTrail::create([
        //     'action' => 'customer_updated',
        //     'model_type' => Customer::class,
        //     'model_id' => $customer->id,
        //     'changes' => $customer->getChanges(),
        // ]);
    }
}
