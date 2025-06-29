<?php

namespace Packages\Store\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoreDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $storeData;

    /**
     * Create a new event instance.
     */
    public function __construct(array $storeData)
    {
        $this->storeData = $storeData;
    }
}
