<?php

namespace Packages\Tenant\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Packages\Tenant\Scopes\TenantScope;

trait HasTenantScope
{
    /**
     * Boot the trait.
     */
    protected static function bootHasTenantScope(): void
    {
        // Add global scope for automatic tenant filtering
        static::addGlobalScope(new TenantScope);

        // Automatically set store_id when creating new records
        static::creating(function (Model $model) {
            if (!$model->getAttribute('store_id')) {
                $storeId = app(\Packages\Tenant\Services\TenantService::class)->getCurrentStoreId();
                if ($storeId) {
                    $model->setAttribute('store_id', $storeId);
                }
            }
        });
    }

    /**
     * Scope query to exclude tenant filtering.
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }

    /**
     * Scope query to specific store.
     */
    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class)->where('store_id', $storeId);
    }

    /**
     * Scope query to multiple stores.
     */
    public function scopeForStores(Builder $query, array $storeIds): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class)->whereIn('store_id', $storeIds);
    }

    /**
     * Get the store relationship.
     */
    public function store()
    {
        return $this->belongsTo(\Packages\Store\Models\Store::class);
    }

    /**
     * Check if model belongs to current store.
     */
    public function belongsToCurrentStore(): bool
    {
        $currentStoreId = app(\Packages\Tenant\Services\TenantService::class)->getCurrentStoreId();
        return $this->store_id === $currentStoreId;
    }

    /**
     * Check if model belongs to specific store.
     */
    public function belongsToStore(int $storeId): bool
    {
        return $this->store_id === $storeId;
    }

    /**
     * Get the tenant column name.
     */
    public function getTenantColumn(): string
    {
        return 'store_id';
    }
}
