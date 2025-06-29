<?php

namespace Packages\Store\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Packages\Store\Models\Store;

class UserAddedToStore
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Store $store;
    public int $userId;
    public string $role;

    /**
     * Create a new event instance.
     */
    public function __construct(Store $store, int $userId, string $role)
    {
        $this->store = $store;
        $this->userId = $userId;
        $this->role = $role;
    }
}
