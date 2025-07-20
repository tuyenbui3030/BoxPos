<?php

namespace Packages\MaterialCatalog\Services;

use Packages\MaterialCatalog\Models\MaterialUnit;
use Packages\MaterialCatalog\Repositories\MaterialUnitRepository;
use Packages\Log\Traits\Loggable;
use Illuminate\Support\Facades\DB;

class MaterialUnitService
{
    use Loggable;

    protected MaterialUnitRepository $materialUnitRepository;

    public function __construct(MaterialUnitRepository $materialUnitRepository)
    {
        $this->materialUnitRepository = $materialUnitRepository;
    }

    /**
     * Create a new material unit.
     */
    public function createMaterialUnit(array $data): MaterialUnit
    {
        $this->logActivity('material_unit_creation_started', [
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->current_store_id,
            'data_keys' => array_keys($data),
        ]);

        DB::beginTransaction();

        try {
            // Add store_id and created_by
            $data['store_id'] = auth()->user()->current_store_id;
            $data['created_by'] = auth()->id();

            // Generate unique code if not provided
            if (empty($data['code'])) {
                $data['code'] = $this->generateUniqueCode($data['type']);
            }

            // Validate uniqueness
            $this->validateUniqueness($data);

            // Handle default unit logic
            if ($data['is_default'] ?? false) {
                $this->unsetOtherDefaults($data['type'], $data['store_id']);
            }

            $unit = $this->materialUnitRepository->create($data);

            DB::commit();

            $this->logActivity('material_unit_created', [
                'unit_id' => $unit->id,
                'unit_code' => $unit->code,
                'user_id' => auth()->id(),
            ]);

            return $unit;
        } catch (\Exception $e) {
            DB::rollback();

            $this->logError($e, [
                'action' => 'create_material_unit',
                'data' => $data,
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * Update an existing material unit.
     */
    public function updateMaterialUnit(MaterialUnit $unit, array $data): MaterialUnit
    {
        $this->logActivity('material_unit_update_started', [
            'unit_id' => $unit->id,
            'user_id' => auth()->id(),
            'data_keys' => array_keys($data),
        ]);

        DB::beginTransaction();

        try {
            // Validate uniqueness (excluding current unit)
            $this->validateUniqueness($data, $unit->id);

            // Handle default unit logic
            if ($data['is_default'] ?? false) {
                $this->unsetOtherDefaults($data['type'], $unit->store_id, $unit->id);
            }

            $unit = $this->materialUnitRepository->update($unit, $data);

            DB::commit();

            $this->logActivity('material_unit_updated', [
                'unit_id' => $unit->id,
                'unit_code' => $unit->code,
                'user_id' => auth()->id(),
            ]);

            return $unit;
        } catch (\Exception $e) {
            DB::rollback();

            $this->logError($e, [
                'action' => 'update_material_unit',
                'unit_id' => $unit->id,
                'data' => $data,
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete a material unit.
     */
    public function deleteMaterialUnit(MaterialUnit $unit): bool
    {
        $this->logActivity('material_unit_deletion_started', [
            'unit_id' => $unit->id,
            'user_id' => auth()->id(),
        ]);

        DB::beginTransaction();

        try {
            // Check if unit is being used
            if ($this->isUnitInUse($unit)) {
                throw new \Exception('Không thể xóa đơn vị tính đang được sử dụng.');
            }

            $result = $this->materialUnitRepository->delete($unit);

            DB::commit();

            $this->logActivity('material_unit_deleted', [
                'unit_id' => $unit->id,
                'unit_code' => $unit->code,
                'user_id' => auth()->id(),
            ]);

            return $result;
        } catch (\Exception $e) {
            DB::rollback();

            $this->logError($e, [
                'action' => 'delete_material_unit',
                'unit_id' => $unit->id,
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * Toggle unit status.
     */
    public function toggleStatus(MaterialUnit $unit): MaterialUnit
    {
        $this->logActivity('material_unit_status_toggle_started', [
            'unit_id' => $unit->id,
            'current_status' => $unit->is_active,
            'user_id' => auth()->id(),
        ]);

        try {
            $unit = $this->materialUnitRepository->update($unit, [
                'is_active' => !$unit->is_active
            ]);

            $this->logActivity('material_unit_status_toggled', [
                'unit_id' => $unit->id,
                'new_status' => $unit->is_active,
                'user_id' => auth()->id(),
            ]);

            return $unit;
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'toggle_material_unit_status',
                'unit_id' => $unit->id,
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate unique code for unit type.
     */
    private function generateUniqueCode(string $type): string
    {
        $prefix = strtoupper(substr($type, 0, 3));
        $counter = 1;

        do {
            $code = $prefix . str_pad($counter, 3, '0', STR_PAD_LEFT);
            $exists = $this->materialUnitRepository->findByCode($code);
            $counter++;
        } while ($exists);

        return $code;
    }

    /**
     * Validate code and name uniqueness.
     */
    private function validateUniqueness(array $data, ?int $excludeId = null): void
    {
        $storeId = $data['store_id'] ?? auth()->user()->current_store_id;

        // Check code uniqueness
        if ($this->materialUnitRepository->codeExists($data['code'], $storeId, $excludeId)) {
            throw new \Exception("Mã đơn vị tính '{$data['code']}' đã tồn tại.");
        }

        // Check name uniqueness
        if ($this->materialUnitRepository->nameExists($data['name'], $storeId, $excludeId)) {
            throw new \Exception("Tên đơn vị tính '{$data['name']}' đã tồn tại.");
        }
    }

    /**
     * Unset other default units of the same type.
     */
    private function unsetOtherDefaults(string $type, int $storeId, ?int $excludeId = null): void
    {
        $this->materialUnitRepository->unsetDefaultsForType($type, $storeId, $excludeId);
    }

    /**
     * Check if unit is being used by materials.
     */
    private function isUnitInUse(MaterialUnit $unit): bool
    {
        return $this->materialUnitRepository->isInUse($unit);
    }
}
