<?php

namespace Packages\Tenant\Services;

use Illuminate\Support\Facades\Cache;
use Packages\Store\Models\Store;

class TenantCacheService
{
    /**
     * Get cache key with tenant prefix.
     */
    public function getTenantCacheKey(string $key, ?Store $store = null): string
    {
        $store = $store ?? app(TenantService::class)->getCurrentStore();
        
        if (!$store) {
            return $key;
        }
        
        return "tenant_{$store->id}_{$key}";
    }

    /**
     * Get cached value with tenant isolation.
     */
    public function get(string $key, $default = null, ?Store $store = null)
    {
        $tenantKey = $this->getTenantCacheKey($key, $store);
        return Cache::get($tenantKey, $default);
    }

    /**
     * Put value in cache with tenant isolation.
     */
    public function put(string $key, $value, $ttl = null, ?Store $store = null): bool
    {
        $tenantKey = $this->getTenantCacheKey($key, $store);
        return Cache::put($tenantKey, $value, $ttl);
    }

    /**
     * Remember value in cache with tenant isolation.
     */
    public function remember(string $key, $ttl, \Closure $callback, ?Store $store = null)
    {
        $tenantKey = $this->getTenantCacheKey($key, $store);
        return Cache::remember($tenantKey, $ttl, $callback);
    }

    /**
     * Forget cached value with tenant isolation.
     */
    public function forget(string $key, ?Store $store = null): bool
    {
        $tenantKey = $this->getTenantCacheKey($key, $store);
        return Cache::forget($tenantKey);
    }

    /**
     * Clear all cache for a specific tenant.
     */
    public function clearTenantCache(?Store $store = null): void
    {
        $store = $store ?? app(TenantService::class)->getCurrentStore();
        
        if (!$store) {
            return;
        }

        $pattern = "tenant_{$store->id}_*";
        
        // For Redis cache
        if (config('cache.default') === 'redis') {
            $redis = Cache::getRedis();
            $keys = $redis->keys($pattern);
            
            if (!empty($keys)) {
                $redis->del($keys);
            }
        } else {
            // For other cache drivers, we need to track keys manually
            // This is a limitation of file/database cache drivers
            logger()->warning('Cache clearing for tenant not fully supported with current cache driver', [
                'driver' => config('cache.default'),
                'tenant_id' => $store->id
            ]);
        }
    }

    /**
     * Get cache tags for tenant (if supported).
     */
    public function getTenantTags(?Store $store = null): array
    {
        $store = $store ?? app(TenantService::class)->getCurrentStore();
        
        if (!$store) {
            return [];
        }
        
        return ["tenant_{$store->id}"];
    }

    /**
     * Cache with tenant tags (if supported).
     */
    public function tagged(string $key, $value, $ttl = null, ?Store $store = null)
    {
        $tags = $this->getTenantTags($store);
        
        if (empty($tags)) {
            return $this->put($key, $value, $ttl, $store);
        }
        
        return Cache::tags($tags)->put($key, $value, $ttl);
    }

    /**
     * Flush cache by tenant tags (if supported).
     */
    public function flushTenantTags(?Store $store = null): void
    {
        $tags = $this->getTenantTags($store);
        
        if (!empty($tags)) {
            Cache::tags($tags)->flush();
        }
    }
}
