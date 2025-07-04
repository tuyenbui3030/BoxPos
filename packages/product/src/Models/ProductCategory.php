<?php

namespace Packages\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Product\Builders\ProductCategoryBuilder;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'parent_id',
        'name',
        'code',
        'description',
        'image_path',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Create a new Eloquent query builder for the model.
     */
    public function newEloquentBuilder($query): ProductCategoryBuilder
    {
        return new ProductCategoryBuilder($query);
    }

    /**
     * Relationships
     */
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrderedBySort($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Helper methods
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function getFullNameAttribute(): string
    {
        if ($this->isRoot()) {
            return $this->name;
        }

        return $this->parent->getFullNameAttribute() . ' > ' . $this->name;
    }

    /**
     * Generate unique category code
     */
    public static function generateCategoryCode(int $storeId): string
    {
        $prefix = 'CAT';
        $lastCategory = static::where('store_id', $storeId)
            ->where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if (!$lastCategory) {
            return $prefix . '001';
        }

        $lastNumber = (int) substr($lastCategory->code, strlen($prefix));
        return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }
}