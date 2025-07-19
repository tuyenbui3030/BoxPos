<?php

namespace Packages\MaterialCatalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialSpecification extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'spec_name',
        'spec_value',
        'spec_unit',
        'spec_type',
        'spec_category',
        'description',
        'sort_order',
        'is_required',
        'is_searchable',
        'show_in_listing',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_required' => 'boolean',
        'is_searchable' => 'boolean',
        'show_in_listing' => 'boolean',
    ];

    /**
     * Get the material that owns this specification.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(BuildingMaterial::class, 'material_id');
    }

    /**
     * Scope for required specifications.
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope for searchable specifications.
     */
    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }

    /**
     * Scope for specifications shown in listing.
     */
    public function scopeInListing($query)
    {
        return $query->where('show_in_listing', true);
    }

    /**
     * Scope by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('spec_category', $category);
    }

    /**
     * Get formatted specification display.
     */
    public function getFormattedValueAttribute(): string
    {
        if ($this->spec_unit) {
            return "{$this->spec_value} {$this->spec_unit}";
        }
        
        return $this->spec_value;
    }

    /**
     * Check if specification is required.
     */
    public function isRequired(): bool
    {
        return $this->is_required;
    }

    /**
     * Check if specification is searchable.
     */
    public function isSearchable(): bool
    {
        return $this->is_searchable;
    }

    /**
     * Check if specification should show in listing.
     */
    public function showInListing(): bool
    {
        return $this->show_in_listing;
    }
}
