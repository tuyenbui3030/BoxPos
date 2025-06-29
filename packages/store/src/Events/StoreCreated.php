<?php

namespace Packages\Store\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Packages\Store\Models\Store;

class StoreCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Store $store;

    /**
     * Create a new event instance.
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }
}
