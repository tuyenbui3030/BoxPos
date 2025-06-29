<?php

namespace Packages\Tenant\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Packages\Store\Services\StoreService;
use Packages\Tenant\Services\TenantService;
use Packages\Store\Exceptions\StoreNotFoundException;

class DomainTenantResolver
{
    public function __construct(
        private StoreService $storeService,
        private TenantService $tenantService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $domain = $this->extractDomain($request);
        
        if (!$domain) {
            return $next($request);
        }

        try {
            // Try to find store by domain
            $store = $this->storeService->getStoreByDomain($domain);
            
            if ($store && $store->isActive()) {
                // Set tenant context based on domain
                $this->tenantService->setCurrentStore($store);
                
                // Add domain context to request
                $request->attributes->set('tenant_domain', $domain);
                $request->attributes->set('tenant_store', $store);
                
                // Set application context
                $this->setApplicationContext($store);
            }
        } catch (StoreNotFoundException $e) {
            // Log domain not found but continue
            logger()->warning('Domain not found', [
                'domain' => $domain,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        return $next($request);
    }

    /**
     * Extract domain from request.
     */
    private function extractDomain(Request $request): ?string
    {
        $host = $request->getHost();
        
        // Remove port if present
        $host = explode(':', $host)[0];
        
        // Skip localhost and IP addresses for development
        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }
        
        return $host;
    }

    /**
     * Set application context based on store.
     */
    private function setApplicationContext($store): void
    {
        // Set locale
        if ($store->language) {
            app()->setLocale($store->language);
        }
        
        // Set timezone
        if ($store->timezone) {
            config(['app.timezone' => $store->timezone]);
            date_default_timezone_set($store->timezone);
        }
        
        // Set currency
        if ($store->currency) {
            config(['app.currency' => $store->currency]);
        }
        
        // Set custom app name for this tenant
        config(['app.name' => $store->name]);
    }
}
