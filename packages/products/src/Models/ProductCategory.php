<?php

namespace Packages\Products\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;

class ProductCategory extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'parent_id',
        'code',
        'name',
        'description',
        'image',
        'color',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    // Helper methods
    public function getHierarchyLevelAttribute(): int
    {
        $level = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }
        
        return $level;
    }

    public function getFullNameAttribute(): string
    {
        $names = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($names, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $names);
    }

    public function getAllChildrenIds(): array
    {
        $childrenIds = [];
        
        foreach ($this->children as $child) {
            $childrenIds[] = $child->id;
            $childrenIds = array_merge($childrenIds, $child->getAllChildrenIds());
        }
        
        return $childrenIds;
    }

    public function getActiveProductsCountAttribute(): int
    {
        return $this->products()->where('status', 'active')->count();
    }

    public function getActiveServicesCountAttribute(): int
    {
        return $this->services()->where('status', 'active')->count();
    }

    public function getTotalItemsCountAttribute(): int
    {
        return $this->active_products_count + $this->active_services_count;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Packages\Products\Database\Factories\ProductCategoryFactory::new();
    }
}
