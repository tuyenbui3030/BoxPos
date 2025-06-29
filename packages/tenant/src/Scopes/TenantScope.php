<?php

namespace Packages\Tenant\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Packages\Tenant\Services\TenantService;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $tenantService = app(TenantService::class);
        $currentStoreId = $tenantService->getCurrentStoreId();

        if ($currentStoreId) {
            $builder->where($model->getTable() . '.store_id', $currentStoreId);
        }
    }

    /**
     * Extend the query builder with the needed functions.
     */
    public function extend(Builder $builder): void
    {
        $this->addWithoutTenantScope($builder);
        $this->addForStore($builder);
        $this->addForStores($builder);
    }

    /**
     * Add the without-tenant-scope extension to the builder.
     */
    protected function addWithoutTenantScope(Builder $builder): void
    {
        $builder->macro('withoutTenantScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the for-store extension to the builder.
     */
    protected function addForStore(Builder $builder): void
    {
        $builder->macro('forStore', function (Builder $builder, int $storeId) {
            return $builder->withoutGlobalScope($this)->where('store_id', $storeId);
        });
    }

    /**
     * Add the for-stores extension to the builder.
     */
    protected function addForStores(Builder $builder): void
    {
        $builder->macro('forStores', function (Builder $builder, array $storeIds) {
            return $builder->withoutGlobalScope($this)->whereIn('store_id', $storeIds);
        });
    }
}
