<?php

namespace Packages\MaterialCatalog\Repositories;

use Packages\MaterialCatalog\Models\MaterialUnit;
use Illuminate\Database\Eloquent\Collection;

class MaterialUnitRepository
{
    /**
     * Create a new material unit.
     */
    public function create(array $data): MaterialUnit
    {
        return MaterialUnit::create($data);
    }

    /**
     * Update an existing material unit.
     */
    public function update(MaterialUnit $unit, array $data): MaterialUnit
    {
        $unit->update($data);
        return $unit->fresh();
    }

    /**
     * Delete a material unit.
     */
    public function delete(MaterialUnit $unit): bool
    {
        return $unit->delete();
    }

    /**
     * Find material unit by ID.
     */
    public function findById(int $id): ?MaterialUnit
    {
        return MaterialUnit::find($id);
    }

    /**
     * Find material unit by code.
     */
    public function findByCode(string $code, ?int $storeId = null): ?MaterialUnit
    {
        $query = MaterialUnit::where('code', $code);
        
        if ($storeId) {
            $query->where('store_id', $storeId);
        }
        
        return $query->first();
    }

    /**
     * Get all material units for a store.
     */
    public function getAllForStore(int $storeId): Collection
    {
        return MaterialUnit::where('store_id', $storeId)
            ->orderByTypeAndName()
            ->get();
    }

    /**
     * Get active material units for a store.
     */
    public function getActiveForStore(int $storeId): Collection
    {
        return MaterialUnit::where('store_id', $storeId)
            ->active()
            ->orderByTypeAndName()
            ->get();
    }

    /**
     * Get material units by type.
     */
    public function getByType(string $type, int $storeId): Collection
    {
        return MaterialUnit::where('store_id', $storeId)
            ->byType($type)
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get default unit for a type.
     */
    public function getDefaultForType(string $type, int $storeId): ?MaterialUnit
    {
        return MaterialUnit::where('store_id', $storeId)
            ->byType($type)
            ->where('is_default', true)
            ->active()
            ->first();
    }

    /**
     * Check if code exists.
     */
    public function codeExists(string $code, int $storeId, ?int $excludeId = null): bool
    {
        $query = MaterialUnit::where('store_id', $storeId)
            ->where('code', $code);
            
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
        $query = MaterialUnit::where('store_id', $storeId)
            ->where('name', $name);
            
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Unset default units for a type.
     */
    public function unsetDefaultsForType(string $type, int $storeId, ?int $excludeId = null): void
    {
        $query = MaterialUnit::where('store_id', $storeId)
            ->where('type', $type)
            ->where('is_default', true);
            
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $query->update(['is_default' => false]);
    }

    /**
     * Check if unit is being used by materials.
     */
    public function isInUse(MaterialUnit $unit): bool
    {
        // Check if used as primary unit
        $usedAsPrimary = \Packages\MaterialCatalog\Models\BuildingMaterial::where('primary_unit_id', $unit->id)->exists();
        
        if ($usedAsPrimary) {
            return true;
        }
        
        // Add other usage checks here (e.g., inventory, pricing, etc.)
        
        return false;
    }

    /**
     * Get units for dropdown.
     */
    public function getForDropdown(int $storeId, ?string $type = null): Collection
    {
        $query = MaterialUnit::where('store_id', $storeId)
            ->active()
            ->select('id', 'code', 'name', 'symbol', 'type');
            
        if ($type) {
            $query->byType($type);
        }
        
        return $query->orderByTypeAndName()->get();
    }

    /**
     * Search units.
     */
    public function search(string $search, int $storeId, int $limit = 10): Collection
    {
        return MaterialUnit::where('store_id', $storeId)
            ->search($search)
            ->active()
            ->limit($limit)
            ->get();
    }

    /**
     * Get conversion factor between two units.
     */
    public function getConversionFactor(MaterialUnit $fromUnit, MaterialUnit $toUnit): ?float
    {
        // Only convert between units of the same type
        if ($fromUnit->type !== $toUnit->type) {
            return null;
        }
        
        // Calculate conversion factor
        return $fromUnit->conversion_factor / $toUnit->conversion_factor;
    }

    /**
     * Convert quantity between units.
     */
    public function convertQuantity(float $quantity, MaterialUnit $fromUnit, MaterialUnit $toUnit): ?float
    {
        $conversionFactor = $this->getConversionFactor($fromUnit, $toUnit);
        
        if ($conversionFactor === null) {
            return null;
        }
        
        return $quantity * $conversionFactor;
    }
}
