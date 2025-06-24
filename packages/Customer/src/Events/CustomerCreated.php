<?php

namespace Packages\Customer\Events;

use Packages\Customer\Models\Customer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Customer Created Event
 * 
 * Fired when a new customer is created.
 */
class CustomerCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The customer instance
     *
     * @var Customer
     */
    public Customer $customer;

    /**
     * Create a new event instance.
     *
     * @param Customer $customer
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('customers'),
        ];
    }
}
