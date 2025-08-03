<?php

namespace Packages\MaterialCatalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;

class MaterialCategory extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'parent_id',
        'code',
        'name',
        'slug',
        'description',
        'image',
        'icon',
        'color',
        'sort_order',
        'is_active',
        'show_in_menu',
        'metadata',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'show_in_menu' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the store that owns this category.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MaterialCategory::class, 'parent_id');
    }

    /**
     * Get child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(MaterialCategory::class, 'parent_id')
                    ->orderBy('sort_order');
    }

    /**
     * Get active child categories.
     */
    public function activeChildren(): HasMany
    {
        return $this->children()->where('is_active', true);
    }

    /**
     * Get materials in this category.
     */
    public function materials(): HasMany
    {
        return $this->hasMany(BuildingMaterial::class, 'category_id');
    }

    /**
     * Get active materials in this category.
     */
    public function activeMaterials(): HasMany
    {
        return $this->materials()->where('is_active', true);
    }

    /**
     * Scope for active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for root categories (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for categories shown in menu.
     */
    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true);
    }

    /**
     * Scope to search categories.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to order by hierarchy (level, sort_order, name).
     */
    public function scopeOrderByHierarchy($query)
    {
        return $query->orderBy('sort_order')
                    ->orderBy('name');
    }

    /**
     * Get all ancestors of this category.
     */
    public function ancestors()
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->prepend($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    /**
     * Get all descendants of this category.
     */
    public function descendants()
    {
        $descendants = collect();
        
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }

        return $descendants;
    }

    /**
     * Get breadcrumb path.
     */
    public function getBreadcrumbAttribute(): array
    {
        $breadcrumb = [];
        $ancestors = $this->ancestors();
        
        foreach ($ancestors as $ancestor) {
            $breadcrumb[] = [
                'id' => $ancestor->id,
                'name' => $ancestor->name,
                'slug' => $ancestor->slug,
            ];
        }
        
        $breadcrumb[] = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];

        return $breadcrumb;
    }

    /**
     * Get category depth level.
     */
    public function getDepthAttribute(): int
    {
        return $this->ancestors()->count();
    }

    /**
     * Check if category has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Check if category has materials.
     */
    public function hasMaterials(): bool
    {
        return $this->materials()->exists();
    }

    /**
     * Get image URL.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        
        return asset('images/default-category.png');
    }

    /**
     * Get total materials count including subcategories.
     */
    public function getTotalMaterialsCountAttribute(): int
    {
        $count = $this->materials()->count();
        
        foreach ($this->children as $child) {
            $count += $child->total_materials_count;
        }

        return $count;
    }

    /**
     * Generate unique slug.
     */
    public static function generateSlug(string $name, int $storeId, ?int $excludeId = null): string
    {
        $baseSlug = \Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        $query = static::where('store_id', $storeId)->where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
            $query = static::where('store_id', $storeId)->where('slug', $slug);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Packages\MaterialCatalog\Database\Factories\MaterialCategoryFactory::new();
    }
}
