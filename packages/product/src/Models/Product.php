<?php

namespace Packages\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Product\Builders\ProductBuilder;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'code',
        'barcode',
        'description',
        'unit',
        'cost_price',
        'sale_price',
        'min_stock',
        'max_stock',
        'track_stock',
        'is_active',
        'attributes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
        'attributes' => 'array',
    ];

    /**
     * Create a new Eloquent query builder for the model.
     */
    public function newEloquentBuilder($query): ProductBuilder
    {
        return new ProductBuilder($query);
    }

    /**
     * Relationships
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
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

    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeTrackStock($query)
    {
        return $query->where('track_stock', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'min_stock');
    }

    /**
     * Helper methods
     */
    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    public function getPrimaryImageAttribute(): ?ProductImage
    {
        return $this->images()->where('is_primary', true)->first();
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_price == 0) {
            return 0;
        }

        return (($this->sale_price - $this->cost_price) / $this->cost_price) * 100;
    }

    /**
     * Generate unique product code
     */
    public static function generateProductCode(int $storeId): string
    {
        $prefix = 'PRD';
        $lastProduct = static::where('store_id', $storeId)
            ->where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if (!$lastProduct) {
            return $prefix . '001';
        }

        $lastNumber = (int) substr($lastProduct->code, strlen($prefix));
        return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }
}