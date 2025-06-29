<?php

namespace Packages\Tenant\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Packages\Tenant\Services\TenantService;
use Packages\Tenant\Traits\HasTenantScope;
use Symfony\Component\HttpFoundation\Response;

class TenantDataIsolation
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply data isolation for authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $currentStore = $this->tenantService->getCurrentStore();

        if ($currentStore) {
            // Apply global scope to all models that use HasTenantScope trait
            $this->applyTenantScope($currentStore->id);
        }

        return $next($request);
    }

    /**
     * Apply tenant scope to all relevant models.
     */
    protected function applyTenantScope(int $storeId): void
    {
        // Get all models that use HasTenantScope trait
        $models = $this->getTenantScopedModels();

        foreach ($models as $modelClass) {
            if (method_exists($modelClass, 'addGlobalScope')) {
                $modelClass::addGlobalScope('tenant', function ($builder) use ($storeId) {
                    $builder->where('store_id', $storeId);
                });
            }
        }
    }

    /**
     * Get all models that should be tenant-scoped.
     */
    protected function getTenantScopedModels(): array
    {
        // This could be made configurable or auto-discovered
        return [
            \Packages\Customer\Models\Customer::class,
            // Add other tenant-scoped models here as they are created
            // \Packages\Product\Models\Product::class,
            // \Packages\Order\Models\Order::class,
            // \Packages\Inventory\Models\InventoryItem::class,
        ];
    }
}
