<?php

namespace Packages\MaterialCatalog\Repositories;

use Packages\MaterialCatalog\Models\BuildingMaterial;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BuildingMaterialRepository
{
    /**
     * Create a new building material.
     */
    public function create(array $data): BuildingMaterial
    {
        return BuildingMaterial::create($data);
    }

    /**
     * Update an existing building material.
     */
    public function update(BuildingMaterial $material, array $data): BuildingMaterial
    {
        $material->update($data);
        return $material->fresh();
    }

    /**
     * Delete a building material.
     */
    public function delete(BuildingMaterial $material): bool
    {
        return $material->delete();
    }

    /**
     * Find building material by ID.
     */
    public function findById(int $id): ?BuildingMaterial
    {
        return BuildingMaterial::find($id);
    }

    /**
     * Find building material by code.
     */
    public function findByCode(string $code, ?int $storeId = null): ?BuildingMaterial
    {
        $query = BuildingMaterial::where('material_code', $code);
        
        if ($storeId) {
            $query->where('store_id', $storeId);
        }
        
        return $query->first();
    }

    /**
     * Get all building materials for a store.
     */
    public function getAllForStore(int $storeId): Collection
    {
        return BuildingMaterial::where('store_id', $storeId)
            ->with(['category', 'primaryUnit'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get active building materials for a store.
     */
    public function getActiveForStore(int $storeId): Collection
    {
        return BuildingMaterial::where('store_id', $storeId)
            ->active()
            ->with(['category', 'primaryUnit'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get featured materials.
     */
    public function getFeatured(int $storeId, int $limit = 10): Collection
    {
        return BuildingMaterial::where('store_id', $storeId)
            ->featured()
            ->active()
            ->with(['category', 'primaryUnit'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get materials by category.
     */
    public function getByCategory(int $categoryId, int $storeId): Collection
    {
        return BuildingMaterial::where('store_id', $storeId)
            ->byCategory($categoryId)
            ->active()
            ->with(['category', 'primaryUnit'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get materials by brand.
     */
    public function getByBrand(string $brand, int $storeId): Collection
    {
        return BuildingMaterial::where('store_id', $storeId)
            ->byBrand($brand)
            ->active()
            ->with(['category', 'primaryUnit'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if material code exists.
     */
    public function codeExists(string $code, int $storeId, ?int $excludeId = null): bool
    {
        $query = BuildingMaterial::where('store_id', $storeId)
            ->where('material_code', $code);
            
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Check if name exists.
     */
    public function nameExists(string $name, int $storeId, ?int $excludeId = null): bool
    {
        $query = BuildingMaterial::where('store_id', $storeId)
            ->where('name', $name);
            
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Check if slug exists.
     */
    public function slugExists(string $slug, int $storeId, ?int $excludeId = null): bool
    {
        $query = BuildingMaterial::where('store_id', $storeId)
            ->where('slug', $slug);
            
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Check if material is being used.
     */
    public function isInUse(BuildingMaterial $material): bool
    {
        // Check if used in inventory
        $usedInInventory = \Packages\MaterialInventory\Models\MaterialInventory::where('material_id', $material->id)->exists();
        
        if ($usedInInventory) {
            return true;
        }
        
        // Check if used in purchase orders
        // $usedInPurchaseOrders = \Packages\MaterialPurchasing\Models\MaterialPurchaseOrderItem::where('material_id', $material->id)->exists();
        
        // Check if used in pricing
        $usedInPricing = \Packages\MaterialPricing\Models\MaterialPricing::where('material_id', $material->id)->exists();
        
        if ($usedInPricing) {
            return true;
        }
        
        return false;
    }

    /**
     * Search materials.
     */
    public function search(string $search, int $storeId, int $limit = 20): Collection
    {
        return BuildingMaterial::where('store_id', $storeId)
            ->search($search)
            ->active()
            ->with(['category', 'primaryUnit'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get materials with filters and pagination.
     */
    public function getWithFilters(array $filters, int $storeId, int $perPage = 15): LengthAwarePaginator
    {
        $query = BuildingMaterial::where('store_id', $storeId)
            ->with(['category', 'primaryUnit']);

        // Apply filters
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        if (!empty($filters['brand'])) {
            $query->byBrand($filters['brand']);
        }

        if (isset($filters['is_active'])) {
            $filters['is_active'] ? $query->active() : $query->where('is_active', false);
        }

        if (isset($filters['is_featured'])) {
            $filters['is_featured'] ? $query->featured() : $query->where('is_featured', false);
        }

        if (isset($filters['is_hazardous'])) {
            $query->where('is_hazardous', $filters['is_hazardous']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        
        if (in_array($sortBy, ['name', 'material_code', 'brand', 'created_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('name');
        }

        return $query->paginate($perPage);
    }

    /**
     * Get distinct brands.
     */
    public function getDistinctBrands(int $storeId): Collection
    {
        return BuildingMaterial::where('store_id', $storeId)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->pluck('brand')
            ->sort()
            ->values();
    }

    /**
     * Get materials for dropdown.
     */
    public function getForDropdown(int $storeId, ?int $categoryId = null): Collection
    {
        $query = BuildingMaterial::where('store_id', $storeId)
            ->active()
            ->select('id', 'material_code', 'name', 'category_id');
            
        if ($categoryId) {
            $query->byCategory($categoryId);
        }
        
        return $query->orderBy('name')->get();
    }

    /**
     * Get low stock materials.
     */
    public function getLowStock(int $storeId): Collection
    {
        return BuildingMaterial::where('store_id', $storeId)
            ->active()
            ->whereHas('inventory', function ($query) {
                $query->lowStock();
            })
            ->with(['category', 'primaryUnit', 'inventory'])
            ->get();
    }

    /**
     * Get materials expiring soon.
     */
    public function getExpiringSoon(int $storeId, int $days = 30): Collection
    {
        return BuildingMaterial::where('store_id', $storeId)
            ->active()
            ->whereNotNull('shelf_life_days')
            ->whereHas('inventory', function ($query) use ($days) {
                $query->expiringSoon($days);
            })
            ->with(['category', 'primaryUnit', 'inventory'])
            ->get();
    }

    /**
     * Get materials by tags.
     */
    public function getByTags(array $tags, int $storeId): Collection
    {
        return BuildingMaterial::where('store_id', $storeId)
            ->active()
            ->where(function ($query) use ($tags) {
                foreach ($tags as $tag) {
                    $query->orWhereJsonContains('tags', $tag);
                }
            })
            ->with(['category', 'primaryUnit'])
            ->get();
    }

    /**
     * Get material statistics.
     */
    public function getStatistics(int $storeId): array
    {
        $total = BuildingMaterial::where('store_id', $storeId)->count();
        $active = BuildingMaterial::where('store_id', $storeId)->active()->count();
        $featured = BuildingMaterial::where('store_id', $storeId)->featured()->count();
        $hazardous = BuildingMaterial::where('store_id', $storeId)->where('is_hazardous', true)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'featured' => $featured,
            'hazardous' => $hazardous,
        ];
    }
}
