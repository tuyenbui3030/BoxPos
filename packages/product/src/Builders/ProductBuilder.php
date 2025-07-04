<?php

namespace Packages\Product\Builders;

use Illuminate\Database\Eloquent\Builder;

class ProductBuilder extends Builder
{
    /**
     * Filter by store (multi-tenant)
     */
    public function forStore(int $storeId): self
    {
        return $this->where('store_id', $storeId);
    }

    /**
     * Filter by active status
     */
    public function active(): self
    {
        return $this->where('is_active', true);
    }

    /**
     * Filter by category
     */
    public function inCategory(int $categoryId): self
    {
        return $this->where('category_id', $categoryId);
    }

    /**
     * Search by name, code, or barcode
     */
    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
        });
    }

    /**
     * Filter by price range
     */
    public function priceRange(float $minPrice, float $maxPrice): self
    {
        return $this->whereBetween('sale_price', [$minPrice, $maxPrice]);
    }

    /**
     * Filter products that track stock
     */
    public function trackStock(): self
    {
        return $this->where('track_stock', true);
    }

    /**
     * Filter low stock products
     */
    public function lowStock(): self
    {
        return $this->whereColumn('current_stock', '<=', 'min_stock');
    }

    /**
     * Order by name
     */
    public function orderByName(): self
    {
        return $this->orderBy('name');
    }

    /**
     * Order by price
     */
    public function orderByPrice(string $direction = 'asc'): self
    {
        return $this->orderBy('sale_price', $direction);
    }

    /**
     * Order by creation date (newest first)
     */
    public function orderByNewest(): self
    {
        return $this->orderBy('created_at', 'desc');
    }

    /**
     * With relationships for performance
     */
    public function withRelations(): self
    {
        return $this->with(['category', 'variants', 'images', 'creator']);
    }

    /**
     * Apply common criteria for listing
     */
    public function applyCriteria(array $criteria): self
    {
        return $this
            ->when(!empty($criteria['search']), fn($q) => $q->search($criteria['search']))
            ->when(!empty($criteria['category_id']), fn($q) => $q->inCategory($criteria['category_id']))
            ->when(isset($criteria['is_active']), fn($q) => $criteria['is_active'] ? $q->active() : $q->where('is_active', false))
            ->when(!empty($criteria['min_price']), fn($q) => $q->where('sale_price', '>=', $criteria['min_price']))
            ->when(!empty($criteria['max_price']), fn($q) => $q->where('sale_price', '<=', $criteria['max_price']))
            ->when(!empty($criteria['track_stock']), fn($q) => $q->trackStock())
            ->when(!empty($criteria['low_stock']), fn($q) => $q->lowStock());
    }
}