<?php

namespace Packages\MaterialCatalog\Services;

use Packages\MaterialCatalog\Models\MaterialCategory;
use Packages\MaterialCatalog\Repositories\MaterialCategoryRepository;
use Packages\Log\Traits\Loggable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MaterialCategoryService
{
    use Loggable;

    protected MaterialCategoryRepository $materialCategoryRepository;

    public function __construct(MaterialCategoryRepository $materialCategoryRepository)
    {
        $this->materialCategoryRepository = $materialCategoryRepository;
    }

    /**
     * Create a new material category.
     */
    public function createMaterialCategory(array $data): MaterialCategory
    {
        $this->logActivity('material_category_creation_started', [
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->current_store_id,
            'data_keys' => array_keys($data),
        ]);

        DB::beginTransaction();

        try {
            // Add store_id and created_by
            $data['store_id'] = auth()->user()->current_store_id;
            $data['created_by'] = auth()->id();

            // Generate slug
            $data['slug'] = $this->generateUniqueSlug($data['name'], $data['store_id']);

            // Calculate level and path
            $this->calculateHierarchy($data);

            // Validate uniqueness
            $this->validateUniqueness($data);

            $category = $this->materialCategoryRepository->create($data);

            DB::commit();

            $this->logActivity('material_category_created', [
                'category_id' => $category->id,
                'category_code' => $category->code,
                'user_id' => auth()->id(),
            ]);

            return $category;
        } catch (\Exception $e) {
            DB::rollback();

            $this->logError($e, [
                'action' => 'create_material_category',
                'data' => $data,
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * Update an existing material category.
     */
    public function updateMaterialCategory(MaterialCategory $category, array $data): MaterialCategory
    {
        $this->logActivity('material_category_update_started', [
            'category_id' => $category->id,
            'user_id' => auth()->id(),
            'data_keys' => array_keys($data),
        ]);

        DB::beginTransaction();

        try {
            // Generate new slug if name changed
            if ($data['name'] !== $category->name) {
                $data['slug'] = $this->generateUniqueSlug($data['name'], $category->store_id, $category->id);
            }

            // Recalculate hierarchy if parent changed
            if (($data['parent_id'] ?? null) !== $category->parent_id) {
                $this->calculateHierarchy($data);
            }

            // Validate uniqueness (excluding current category)
            $this->validateUniqueness($data, $category->id);

            $category = $this->materialCategoryRepository->update($category, $data);

            // Update children hierarchy if needed
            if (isset($data['parent_id']) && $data['parent_id'] !== $category->getOriginal('parent_id')) {
                $this->updateChildrenHierarchy($category);
            }

            DB::commit();

            $this->logActivity('material_category_updated', [
                'category_id' => $category->id,
                'category_code' => $category->code,
                'user_id' => auth()->id(),
            ]);

            return $category;
        } catch (\Exception $e) {
            DB::rollback();

            $this->logError($e, [
                'action' => 'update_material_category',
                'category_id' => $category->id,
                'data' => $data,
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete a material category.
     */
    public function deleteMaterialCategory(MaterialCategory $category): bool
    {
        $this->logActivity('material_category_deletion_started', [
            'category_id' => $category->id,
            'user_id' => auth()->id(),
        ]);

        DB::beginTransaction();

        try {
            // Check if category has children
            if ($this->hasChildren($category)) {
                throw new \Exception('Không thể xóa danh mục có danh mục con.');
            }

            // Check if category is being used
            if ($this->isCategoryInUse($category)) {
                throw new \Exception('Không thể xóa danh mục đang được sử dụng.');
            }

            $result = $this->materialCategoryRepository->delete($category);

            DB::commit();

            $this->logActivity('material_category_deleted', [
                'category_id' => $category->id,
                'category_code' => $category->code,
                'user_id' => auth()->id(),
            ]);

            return $result;
        } catch (\Exception $e) {
            DB::rollback();

            $this->logError($e, [
                'action' => 'delete_material_category',
                'category_id' => $category->id,
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * Toggle category status.
     */
    public function toggleStatus(MaterialCategory $category): MaterialCategory
    {
        $this->logActivity('material_category_status_toggle_started', [
            'category_id' => $category->id,
            'current_status' => $category->is_active,
            'user_id' => auth()->id(),
        ]);

        try {
            $category = $this->materialCategoryRepository->update($category, [
                'is_active' => !$category->is_active
            ]);

            $this->logActivity('material_category_status_toggled', [
                'category_id' => $category->id,
                'new_status' => $category->is_active,
                'user_id' => auth()->id(),
            ]);

            return $category;
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'toggle_material_category_status',
                'category_id' => $category->id,
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate unique slug.
     */
    private function generateUniqueSlug(string $name, int $storeId, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->materialCategoryRepository->slugExists($slug, $storeId, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Calculate hierarchy level and path.
     */
    private function calculateHierarchy(array &$data): void
    {
        if (empty($data['parent_id'])) {
            $data['level'] = 0;
            $data['path'] = '';
        } else {
            $parent = $this->materialCategoryRepository->findById($data['parent_id']);
            if (!$parent) {
                throw new \Exception('Danh mục cha không tồn tại.');
            }

            $data['level'] = $parent->level + 1;
            $data['path'] = $parent->path ? $parent->path . ',' . $parent->id : (string)$parent->id;
        }
    }

    /**
     * Update children hierarchy after parent change.
     */
    private function updateChildrenHierarchy(MaterialCategory $category): void
    {
        $children = $this->materialCategoryRepository->getChildren($category);
        
        foreach ($children as $child) {
            $newLevel = $category->level + 1;
            $newPath = $category->path ? $category->path . ',' . $category->id : (string)$category->id;
            
            $this->materialCategoryRepository->update($child, [
                'level' => $newLevel,
                'path' => $newPath,
            ]);
            
            // Recursively update grandchildren
            $this->updateChildrenHierarchy($child);
        }
    }

    /**
     * Validate code and name uniqueness.
     */
    private function validateUniqueness(array $data, ?int $excludeId = null): void
    {
        $storeId = $data['store_id'] ?? auth()->user()->current_store_id;

        // Check code uniqueness
        if ($this->materialCategoryRepository->codeExists($data['code'], $storeId, $excludeId)) {
            throw new \Exception("Mã danh mục '{$data['code']}' đã tồn tại.");
        }

        // Check name uniqueness within same parent
        if ($this->materialCategoryRepository->nameExistsInParent($data['name'], $data['parent_id'] ?? null, $storeId, $excludeId)) {
            throw new \Exception("Tên danh mục '{$data['name']}' đã tồn tại trong cùng danh mục cha.");
        }
    }

    /**
     * Check if category has children.
     */
    private function hasChildren(MaterialCategory $category): bool
    {
        return $this->materialCategoryRepository->hasChildren($category);
    }

    /**
     * Check if category is being used by materials.
     */
    private function isCategoryInUse(MaterialCategory $category): bool
    {
        return $this->materialCategoryRepository->isInUse($category);
    }
}
