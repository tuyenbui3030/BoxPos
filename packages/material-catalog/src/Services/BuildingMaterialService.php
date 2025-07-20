<?php

namespace Packages\MaterialCatalog\Services;

use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\MaterialCatalog\Repositories\BuildingMaterialRepository;
use Packages\Log\Traits\Loggable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BuildingMaterialService
{
    use Loggable;

    protected BuildingMaterialRepository $buildingMaterialRepository;

    public function __construct(BuildingMaterialRepository $buildingMaterialRepository)
    {
        $this->buildingMaterialRepository = $buildingMaterialRepository;
    }

    /**
     * Create a new building material.
     */
    public function createBuildingMaterial(array $data, array $images = []): BuildingMaterial
    {
        $this->logActivity('building_material_creation_started', [
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->current_store_id,
            'data_keys' => array_keys($data),
            'image_count' => count($images),
        ]);

        DB::beginTransaction();

        try {
            // Add store_id and created_by
            $data['store_id'] = auth()->user()->current_store_id;
            $data['created_by'] = auth()->id();

            // Generate slug
            $data['slug'] = $this->generateUniqueSlug($data['name'], $data['store_id']);

            // Validate uniqueness
            $this->validateUniqueness($data);

            // Handle image uploads
            if (!empty($images)) {
                $data['images'] = $this->uploadImages($images);
            }

            $material = $this->buildingMaterialRepository->create($data);

            DB::commit();

            $this->logActivity('building_material_created', [
                'material_id' => $material->id,
                'material_code' => $material->material_code,
                'user_id' => auth()->id(),
            ]);

            return $material;
        } catch (\Exception $e) {
            DB::rollback();

            // Clean up uploaded images on error
            if (isset($data['images'])) {
                $this->cleanupImages($data['images']);
            }

            $this->logError($e, [
                'action' => 'create_building_material',
                'data' => $data,
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * Update an existing building material.
     */
    public function updateBuildingMaterial(BuildingMaterial $material, array $data, array $images = []): BuildingMaterial
    {
        $this->logActivity('building_material_update_started', [
            'material_id' => $material->id,
            'user_id' => auth()->id(),
            'data_keys' => array_keys($data),
            'image_count' => count($images),
        ]);

        DB::beginTransaction();

        try {
            // Generate new slug if name changed
            if ($data['name'] !== $material->name) {
                $data['slug'] = $this->generateUniqueSlug($data['name'], $material->store_id, $material->id);
            }

            // Validate uniqueness (excluding current material)
            $this->validateUniqueness($data, $material->id);

            // Handle image uploads
            $oldImages = $material->images ?? [];
            if (!empty($images)) {
                $newImages = $this->uploadImages($images);
                $data['images'] = array_merge($oldImages, $newImages);
            }

            $material = $this->buildingMaterialRepository->update($material, $data);

            DB::commit();

            $this->logActivity('building_material_updated', [
                'material_id' => $material->id,
                'material_code' => $material->material_code,
                'user_id' => auth()->id(),
            ]);

            return $material;
        } catch (\Exception $e) {
            DB::rollback();

            // Clean up newly uploaded images on error
            if (isset($newImages)) {
                $this->cleanupImages($newImages);
            }

            $this->logError($e, [
                'action' => 'update_building_material',
                'material_id' => $material->id,
                'data' => $data,
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete a building material.
     */
    public function deleteBuildingMaterial(BuildingMaterial $material): bool
    {
        $this->logActivity('building_material_deletion_started', [
            'material_id' => $material->id,
            'user_id' => auth()->id(),
        ]);

        DB::beginTransaction();

        try {
            // Check if material is being used
            if ($this->isMaterialInUse($material)) {
                throw new \Exception('Không thể xóa vật liệu đang được sử dụng.');
            }

            // Store images for cleanup
            $imagesToDelete = $material->images ?? [];

            $result = $this->buildingMaterialRepository->delete($material);

            // Clean up images
            if (!empty($imagesToDelete)) {
                $this->cleanupImages($imagesToDelete);
            }

            DB::commit();

            $this->logActivity('building_material_deleted', [
                'material_id' => $material->id,
                'material_code' => $material->material_code,
                'user_id' => auth()->id(),
            ]);

            return $result;
        } catch (\Exception $e) {
            DB::rollback();

            $this->logError($e, [
                'action' => 'delete_building_material',
                'material_id' => $material->id,
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * Toggle material status.
     */
    public function toggleStatus(BuildingMaterial $material): BuildingMaterial
    {
        $this->logActivity('building_material_status_toggle_started', [
            'material_id' => $material->id,
            'current_status' => $material->is_active,
            'user_id' => auth()->id(),
        ]);

        try {
            $material = $this->buildingMaterialRepository->update($material, [
                'is_active' => !$material->is_active
            ]);

            $this->logActivity('building_material_status_toggled', [
                'material_id' => $material->id,
                'new_status' => $material->is_active,
                'user_id' => auth()->id(),
            ]);

            return $material;
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'toggle_building_material_status',
                'material_id' => $material->id,
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * Upload material images.
     */
    private function uploadImages(array $images): array
    {
        $uploadedImages = [];

        foreach ($images as $image) {
            if ($image && $image->isValid()) {
                $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('materials', $filename, 'public');
                $uploadedImages[] = $path;
            }
        }

        return $uploadedImages;
    }

    /**
     * Clean up uploaded images.
     */
    private function cleanupImages(array $images): void
    {
        foreach ($images as $image) {
            if (Storage::disk('public')->exists($image)) {
                Storage::disk('public')->delete($image);
            }
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

        while ($this->buildingMaterialRepository->slugExists($slug, $storeId, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Validate code and name uniqueness.
     */
    private function validateUniqueness(array $data, ?int $excludeId = null): void
    {
        $storeId = $data['store_id'] ?? auth()->user()->current_store_id;

        // Check material code uniqueness
        if ($this->buildingMaterialRepository->codeExists($data['material_code'], $storeId, $excludeId)) {
            throw new \Exception("Mã vật liệu '{$data['material_code']}' đã tồn tại.");
        }

        // Check name uniqueness
        if ($this->buildingMaterialRepository->nameExists($data['name'], $storeId, $excludeId)) {
            throw new \Exception("Tên vật liệu '{$data['name']}' đã tồn tại.");
        }
    }

    /**
     * Check if material is being used.
     */
    private function isMaterialInUse(BuildingMaterial $material): bool
    {
        return $this->buildingMaterialRepository->isInUse($material);
    }
}
