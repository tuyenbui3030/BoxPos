<?php

namespace Packages\Products\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\User\Models\User;

class Product extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'category_id',
        'sku',
        'barcode',
        'name',
        'description',
        'short_description',
        'type',
        'status',
        'is_digital',
        'is_service',
        'cost_price',
        'selling_price',
        'sale_price',
        'sale_price_start',
        'sale_price_end',
        'currency',
        'is_taxable',
        'tax_rate',
        'tax_class',
        'track_inventory',
        'stock_quantity',
        'reserved_quantity',
        'available_quantity',
        'unit',
        'min_stock_level',
        'max_stock_level',
        'reorder_point',
        'reorder_quantity',
        'weight',
        'weight_unit',
        'length',
        'width',
        'height',
        'dimension_unit',
        'images',
        'featured_image',
        'meta_title',
        'meta_description',
        'tags',
        'supplier_sku',
        'manufacturer',
        'brand',
        'model',
        'allow_backorder',
        'is_featured',
        'requires_shipping',
        'sort_order',
        'attributes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'sale_price_start' => 'date',
        'sale_price_end' => 'date',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'stock_quantity' => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'available_quantity' => 'decimal:3',
        'min_stock_level' => 'decimal:3',
        'max_stock_level' => 'decimal:3',
        'reorder_point' => 'decimal:3',
        'reorder_quantity' => 'decimal:3',
        'weight' => 'decimal:3',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'is_digital' => 'boolean',
        'is_service' => 'boolean',
        'is_taxable' => 'boolean',
        'track_inventory' => 'boolean',
        'allow_backorder' => 'boolean',
        'is_featured' => 'boolean',
        'requires_shipping' => 'boolean',
        'images' => 'array',
        'tags' => 'array',
        'attributes' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('available_quantity', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('available_quantity', '<=', 'min_stock_level');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('available_quantity', '<=', 0);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Helper methods
    public static function generateSKU(int $storeId): string
    {
        $lastProduct = static::where('store_id', $storeId)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastProduct ? ($lastProduct->id + 1) : 1;
        return 'PRD' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function updateAvailableQuantity(): void
    {
        $this->available_quantity = $this->stock_quantity - $this->reserved_quantity;
        $this->save();
    }

    public function getCurrentPriceAttribute(): float
    {
        if ($this->sale_price && $this->isOnSale()) {
            return $this->sale_price;
        }
        
        return $this->selling_price;
    }

    public function isOnSale(): bool
    {
        if (!$this->sale_price) {
            return false;
        }
        
        $now = now()->toDateString();
        
        if ($this->sale_price_start && $this->sale_price_start > $now) {
            return false;
        }
        
        if ($this->sale_price_end && $this->sale_price_end < $now) {
            return false;
        }
        
        return true;
    }

    public function isInStock(): bool
    {
        return $this->available_quantity > 0;
    }

    public function isLowStock(): bool
    {
        return $this->available_quantity <= $this->min_stock_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->available_quantity <= 0;
    }

    public function needsReorder(): bool
    {
        return $this->available_quantity <= $this->reorder_point;
    }

    public function canSell(float $quantity = 1): bool
    {
        if (!$this->track_inventory) {
            return true;
        }
        
        if ($this->allow_backorder) {
            return true;
        }
        
        return $this->available_quantity >= $quantity;
    }

    public function hasVariants(): bool
    {
        return $this->type === 'variable' && $this->variants()->count() > 0;
    }

    public function getDefaultVariant(): ?ProductVariant
    {
        return $this->variants()->where('is_default', true)->first();
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->current_price <= 0) {
            return 0;
        }
        
        return (($this->current_price - $this->cost_price) / $this->current_price) * 100;
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->current_price, 0, ',', '.') . ' ' . $this->currency;
    }

    public function getStockStatusAttribute(): string
    {
        if (!$this->track_inventory) {
            return 'Không theo dõi';
        }
        
        if ($this->isOutOfStock()) {
            return 'Hết hàng';
        }
        
        if ($this->isLowStock()) {
            return 'Sắp hết';
        }
        
        return 'Còn hàng';
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Packages\Products\Database\Factories\ProductFactory::new();
    }
}
