<?php

namespace Packages\Product\Builders;

use Illuminate\Database\Eloquent\Builder;

class ProductCategoryBuilder extends Builder
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
     * Filter by inactive status
     */
    public function inactive(): self
    {
        return $this->where('is_active', false);
    }

    /**
     * Filter root categories (no parent)
     */
    public function rootCategories(): self
    {
        return $this->whereNull('parent_id');
    }

    /**
     * Filter child categories (has parent)
     */
    public function childCategories(): self
    {
        return $this->whereNotNull('parent_id');
    }

    /**
     * Filter by parent category
     */
    public function byParent(int $parentId): self
    {
        return $this->where('parent_id', $parentId);
    }

    /**
     * Search by name or code
     */
    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        });
    }

    /**
     * Order by sort order and name
     */
    public function orderBySort(): self
    {
        return $this->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Order by name
     */
    public function orderByName(): self
    {
        return $this->orderBy('name');
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
        return $this->with(['parent', 'children', 'creator', 'updater']);
    }

    /**
     * With product count
     */
    public function withProductCount(): self
    {
        return $this->withCount('products');
    }

    /**
     * Apply common criteria for listing
     */
    public function applyCriteria(array $criteria): self
    {
        return $this
            ->when(!empty($criteria['search']), fn($q) => $q->search($criteria['search']))
            ->when(!empty($criteria['parent_id']), fn($q) => $q->byParent($criteria['parent_id']))
            ->when(isset($criteria['is_active']), fn($q) => $criteria['is_active'] ? $q->active() : $q->inactive())
            ->when(!empty($criteria['root_only']), fn($q) => $q->rootCategories());
    }

    /**
     * Get hierarchical tree structure
     */
    public function getTree(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->rootCategories()
            ->with(['children' => function ($query) {
                $query->orderBySort();
            }])
            ->orderBySort()
            ->get();
    }
}