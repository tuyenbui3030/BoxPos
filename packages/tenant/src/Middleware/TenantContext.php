<?php

namespace Packages\Tenant\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Packages\Tenant\Services\TenantService;
use Symfony\Component\HttpFoundation\Response;

class TenantContext
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
        // Only apply tenant context for authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        // Initialize tenant context
        $this->tenantService->initializeTenantContext();

        // Check if user has access to any store
        $currentStore = $this->tenantService->getCurrentStore();
        
        if (!$currentStore && $this->requiresStoreAccess($request)) {
            // Redirect to store selection if no current store
            return redirect()->route('store.selection');
        }

        // Add store context to request
        if ($currentStore) {
            $request->attributes->set('current_store', $currentStore);
            $request->attributes->set('store_id', $currentStore->id);
        }

        return $next($request);
    }

    /**
     * Check if the current route requires store access.
     */
    protected function requiresStoreAccess(Request $request): bool
    {
        // Routes that don't require store access
        $excludedRoutes = [
            'store.selection',
            'store.switch',
            'logout',
            'profile.*',
            'api.*', // API routes handle their own store context
        ];

        $currentRoute = $request->route()?->getName();

        if (!$currentRoute) {
            return true; // Require store access for unnamed routes
        }

        foreach ($excludedRoutes as $pattern) {
            if (fnmatch($pattern, $currentRoute)) {
                return false;
            }
        }

        return true;
    }
}
