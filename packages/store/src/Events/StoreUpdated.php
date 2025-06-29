<?php

namespace Packages\Store\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Packages\Store\Models\Store;

class StoreUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Store $store;
    public array $originalData;

    /**
     * Create a new event instance.
     */
    public function __construct(Store $store, array $originalData)
    {
        $this->store = $store;
        $this->originalData = $originalData;
    }
}
