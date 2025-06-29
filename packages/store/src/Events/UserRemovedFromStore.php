<?php

namespace Packages\Store\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Packages\Store\Models\Store;

class UserRemovedFromStore
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Store $store;
    public int $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(Store $store, int $userId)
    {
        $this->store = $store;
        $this->userId = $userId;
    }
}
