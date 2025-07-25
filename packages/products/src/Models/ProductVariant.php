<?php

namespace Packages\Products\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'name',
        'description',
        'attributes',
        'attribute_summary',
        'cost_price',
        'selling_price',
        'sale_price',
        'sale_price_start',
        'sale_price_end',
        'stock_quantity',
        'reserved_quantity',
        'available_quantity',
        'min_stock_level',
        'reorder_point',
        'weight',
        'length',
        'width',
        'height',
        'images',
        'featured_image',
        'status',
        'is_default',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'sale_price_start' => 'date',
        'sale_price_end' => 'date',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'available_quantity' => 'decimal:3',
        'min_stock_level' => 'decimal:3',
        'reorder_point' => 'decimal:3',
        'weight' => 'decimal:3',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'is_default' => 'boolean',
        'attributes' => 'array',
        'images' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('available_quantity', '>', 0);
    }

    // Helper methods
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
        if (!$this->product->track_inventory) {
            return true;
        }
        
        if ($this->product->allow_backorder) {
            return true;
        }
        
        return $this->available_quantity >= $quantity;
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->attribute_summary) {
            return $this->product->name . ' - ' . $this->attribute_summary;
        }
        
        return $this->name ?: $this->product->name;
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->current_price, 0, ',', '.') . ' ' . $this->product->currency;
    }

    public function getStockStatusAttribute(): string
    {
        if (!$this->product->track_inventory) {
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

    public function getProfitMarginAttribute(): float
    {
        if ($this->current_price <= 0) {
            return 0;
        }
        
        return (($this->current_price - $this->cost_price) / $this->current_price) * 100;
    }
}
