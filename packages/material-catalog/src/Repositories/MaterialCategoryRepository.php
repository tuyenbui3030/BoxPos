<?php

namespace Packages\MaterialCatalog\Repositories;

use Packages\MaterialCatalog\Models\MaterialCategory;
use Illuminate\Database\Eloquent\Collection;

class MaterialCategoryRepository
{
    /**
     * Create a new material category.
     */
    public function create(array $data): MaterialCategory
    {
        return MaterialCategory::create($data);
    }

    /**
     * Update an existing material category.
     */
    public function update(MaterialCategory $category, array $data): MaterialCategory
    {
        $category->update($data);
        return $category->fresh();
    }

    /**
     * Delete a material category.
     */
    public function delete(MaterialCategory $category): bool
    {
        return $category->delete();
    }

    /**
     * Find material category by ID.
     */
    public function findById(int $id): ?MaterialCategory
    {
        return MaterialCategory::find($id);
    }

    /**
     * Find material category by code.
     */
    public function findByCode(string $code, ?int $storeId = null): ?MaterialCategory
    {
        $query = MaterialCategory::where('code', $code);
        
        if ($storeId) {
            $query->where('store_id', $storeId);
        }
        
        return $query->first();
    }

    /**
     * Get all material categories for a store.
     */
    public function getAllForStore(int $storeId): Collection
    {
        return MaterialCategory::where('store_id', $storeId)
            ->orderByHierarchy()
            ->get();
    }

    /**
     * Get active material categories for a store.
     */
    public function getActiveForStore(int $storeId): Collection
    {
        return MaterialCategory::where('store_id', $storeId)
            ->active()
            ->orderByHierarchy()
            ->get();
    }

    /**
     * Get root categories (no parent).
     */
    public function getRootCategories(int $storeId): Collection
    {
        return MaterialCategory::where('store_id', $storeId)
            ->whereNull('parent_id')
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get children of a category.
     */
    public function getChildren(MaterialCategory $category): Collection
    {
        return MaterialCategory::where('parent_id', $category->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get categories by level.
     */
    public function getByLevel(int $level, int $storeId): Collection
    {
        return MaterialCategory::where('store_id', $storeId)
            ->where('level', $level)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if code exists.
     */
    public function codeExists(string $code, int $storeId, ?int $excludeId = null): bool
    {
        $query = MaterialCategory::where('store_id', $storeId)
            ->where('code', $code);
            
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Check if name exists within same parent.
     */
    public function nameExistsInParent(string $name, ?int $parentId, int $storeId, ?int $excludeId = null): bool
    {
        $query = MaterialCategory::where('store_id', $storeId)
            ->where('name', $name)
            ->where('parent_id', $parentId);
            
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
        $query = MaterialCategory::where('store_id', $storeId)
            ->where('slug', $slug);
            
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Check if category has children.
     */
    public function hasChildren(MaterialCategory $category): bool
    {
        return MaterialCategory::where('parent_id', $category->id)->exists();
    }

    /**
     * Check if category is being used by materials.
     */
    public function isInUse(MaterialCategory $category): bool
    {
        // Check if used by building materials
        $usedByMaterials = \Packages\MaterialCatalog\Models\BuildingMaterial::where('category_id', $category->id)->exists();
        
        if ($usedByMaterials) {
            return true;
        }
        
        // Add other usage checks here
        
        return false;
    }

    /**
     * Get categories for dropdown.
     */
    public function getForDropdown(int $storeId, ?int $parentId = null): Collection
    {
        $query = MaterialCategory::where('store_id', $storeId)
            ->active()
            ->select('id', 'parent_id', 'name', 'level');
            
        if ($parentId !== null) {
            $query->where('parent_id', $parentId);
        }
        
        return $query->orderByHierarchy()->get();
    }

    /**
     * Search categories.
     */
    public function search(string $search, int $storeId, int $limit = 10): Collection
    {
        return MaterialCategory::where('store_id', $storeId)
            ->search($search)
            ->active()
            ->limit($limit)
            ->get();
    }

    /**
     * Get category tree structure.
     */
    public function getTree(int $storeId, ?int $parentId = null): Collection
    {
        return MaterialCategory::where('store_id', $storeId)
            ->where('parent_id', $parentId)
            ->active()
            ->with(['children' => function ($query) {
                $query->active()->orderBy('sort_order')->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get full path of category.
     */
    public function getFullPath(MaterialCategory $category): string
    {
        $path = [];
        $current = $category;
        
        while ($current) {
            array_unshift($path, $current->name);
            $current = $current->parent;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Move category to new parent.
     */
    public function moveToParent(MaterialCategory $category, ?int $newParentId): MaterialCategory
    {
        $category->update(['parent_id' => $newParentId]);
        
        // Recalculate level and path for this category and its children
        $this->recalculateHierarchy($category);
        
        return $category->fresh();
    }

    /**
     * Recalculate hierarchy for category and its descendants.
     */
    private function recalculateHierarchy(MaterialCategory $category): void
    {
        // Calculate new level and path
        if ($category->parent_id) {
            $parent = $this->findById($category->parent_id);
            $newLevel = $parent->level + 1;
            $newPath = $parent->path ? $parent->path . ',' . $parent->id : (string)$parent->id;
        } else {
            $newLevel = 0;
            $newPath = '';
        }
        
        // Update category
        $category->update([
            'level' => $newLevel,
            'path' => $newPath,
        ]);
        
        // Update all children recursively
        $children = $this->getChildren($category);
        foreach ($children as $child) {
            $this->recalculateHierarchy($child);
        }
    }
}
