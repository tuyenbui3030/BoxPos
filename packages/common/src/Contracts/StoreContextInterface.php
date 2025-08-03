<?php

namespace Packages\Common\Contracts;

use App\Models\Store;

interface StoreContextInterface
{
    /**
     * Get the current store
     */
    public function getCurrentStore(): ?Store;

    /**
     * Switch to a different store
     */
    public function switchStore(int $storeId): bool;

    /**
     * Check if user has access to a store
     */
    public function hasStoreAccess(int $storeId): bool;
}