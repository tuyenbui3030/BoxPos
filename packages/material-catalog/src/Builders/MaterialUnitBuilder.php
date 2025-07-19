<?php

namespace Packages\MaterialCatalog\Builders;

use Illuminate\Database\Eloquent\Builder;

class MaterialUnitBuilder extends Builder
{
    /**
     * Filter by active units.
     */
    public function active(): self
    {
        return $this->where('is_active', true);
    }

    /**
     * Filter by inactive units.
     */
    public function inactive(): self
    {
        return $this->where('is_active', false);
    }

    /**
     * Filter by default units.
     */
    public function defaults(): self
    {
        return $this->where('is_default', true);
    }

    /**
     * Filter by unit type.
     */
    public function byType(string $type): self
    {
        return $this->where('type', $type);
    }

    /**
     * Filter by multiple types.
     */
    public function byTypes(array $types): self
    {
        return $this->whereIn('type', $types);
    }

    /**
     * Search units by code, name, or symbol.
     */
    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('symbol', 'like', "%{$search}%");
        });
    }

    /**
     * Filter by conversion factor range.
     */
    public function conversionFactorBetween(float $min, float $max): self
    {
        return $this->whereBetween('conversion_factor', [$min, $max]);
    }

    /**
     * Order by type and name.
     */
    public function orderByTypeAndName(): self
    {
        return $this->orderBy('type')->orderBy('name');
    }

    /**
     * Order by conversion factor.
     */
    public function orderByConversionFactor(string $direction = 'asc'): self
    {
        return $this->orderBy('conversion_factor', $direction);
    }

    /**
     * Apply multiple criteria filters.
     */
    public function applyCriteria(array $criteria): self
    {
        return $this
            ->when(!empty($criteria['search']), fn($q) => $q->search($criteria['search']))
            ->when(!empty($criteria['type']), fn($q) => $q->byType($criteria['type']))
            ->when(!empty($criteria['types']), fn($q) => $q->byTypes($criteria['types']))
            ->when(isset($criteria['is_active']), fn($q) => $criteria['is_active'] ? $q->active() : $q->inactive())
            ->when(isset($criteria['is_default']), fn($q) => $criteria['is_default'] ? $q->defaults() : $q->where('is_default', false));
    }

    /**
     * Get units for dropdown/select options.
     */
    public function forDropdown(): self
    {
        return $this->active()
                    ->select('id', 'code', 'name', 'symbol', 'type')
                    ->orderByTypeAndName();
    }

    /**
     * Get weight units.
     */
    public function weightUnits(): self
    {
        return $this->byType('weight')->active();
    }

    /**
     * Get volume units.
     */
    public function volumeUnits(): self
    {
        return $this->byType('volume')->active();
    }

    /**
     * Get area units.
     */
    public function areaUnits(): self
    {
        return $this->byType('area')->active();
    }

    /**
     * Get length units.
     */
    public function lengthUnits(): self
    {
        return $this->byType('length')->active();
    }

    /**
     * Get count units.
     */
    public function countUnits(): self
    {
        return $this->byType('count')->active();
    }
}
