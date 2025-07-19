<?php

namespace Packages\MaterialCatalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\MaterialCatalog\Builders\MaterialUnitBuilder;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;

class MaterialUnit extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'code',
        'name',
        'symbol',
        'type',
        'conversion_factor',
        'base_unit_code',
        'description',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:4',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    const TYPE_WEIGHT = 'weight';
    const TYPE_VOLUME = 'volume';
    const TYPE_AREA = 'area';
    const TYPE_LENGTH = 'length';
    const TYPE_COUNT = 'count';

    /**
     * Create a new Eloquent query builder for the model.
     */
    public function newEloquentBuilder($query)
    {
        return new MaterialUnitBuilder($query);
    }

    /**
     * Get the store that owns this unit.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get materials that use this unit.
     */
    public function materials(): HasMany
    {
        return $this->hasMany(BuildingMaterial::class, 'primary_unit_id');
    }

    /**
     * Scope for active units.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default units.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope by unit type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to search units.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('symbol', 'like', "%{$search}%");
        });
    }

    /**
     * Convert quantity from this unit to another unit.
     */
    public function convertTo(MaterialUnit $targetUnit, float $quantity): float
    {
        if ($this->type !== $targetUnit->type) {
            throw new \InvalidArgumentException('Cannot convert between different unit types');
        }

        // Convert to base unit first, then to target unit
        $baseQuantity = $quantity * $this->conversion_factor;
        return $baseQuantity / $targetUnit->conversion_factor;
    }

    /**
     * Check if this unit can be converted to another unit.
     */
    public function canConvertTo(MaterialUnit $targetUnit): bool
    {
        return $this->type === $targetUnit->type;
    }

    /**
     * Get formatted unit display.
     */
    public function getDisplayAttribute(): string
    {
        return $this->symbol ? "{$this->name} ({$this->symbol})" : $this->name;
    }

    /**
     * Check if unit is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if unit is default for its type.
     */
    public function isDefault(): bool
    {
        return $this->is_default;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Packages\MaterialCatalog\Database\Factories\MaterialUnitFactory::new();
    }
}
