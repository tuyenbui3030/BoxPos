<?php

namespace Packages\MaterialInventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\MaterialInventory\Database\Factories\MaterialInventoryFactory;

class MaterialInventory extends Model
{
    use HasFactory, HasTenantScope;

    protected $table = 'material_inventory';

    protected $fillable = [
        'store_id',
        'material_id',
        'location_code',
        'location_name',
        'zone',
        'aisle',
        'shelf',
        'bin',
        'quantity_on_hand',
        'quantity_reserved',
        'quantity_available',
        'quantity_incoming',
        'quantity_outgoing',
        'batch_number',
        'serial_numbers',
        'manufacture_date',
        'expiry_date',
        'received_date',
        'unit_cost',
        'total_cost',
        'cost_method',
        'condition',
        'quality_checked',
        'last_quality_check',
        'quality_notes',
        'min_stock_level',
        'max_stock_level',
        'last_movement_at',
        'movement_count',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'quantity_on_hand' => 'decimal:3',
        'quantity_reserved' => 'decimal:3',
        'quantity_available' => 'decimal:3',
        'quantity_incoming' => 'decimal:3',
        'quantity_outgoing' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'quality_checked' => 'boolean',
        'min_stock_level' => 'decimal:3',
        'max_stock_level' => 'decimal:3',
        'movement_count' => 'integer',
        'is_active' => 'boolean',
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'received_date' => 'date',
        'last_quality_check' => 'date',
        'last_movement_at' => 'datetime',
    ];

    /**
     * Get the store that owns this inventory.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the material for this inventory.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(BuildingMaterial::class, 'material_id');
    }

    /**
     * Scope for active inventory.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for low stock items.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity_available', '<=', 'min_stock_level');
    }

    /**
     * Scope for out of stock items.
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity_available', '<=', 0);
    }

    /**
     * Scope by location.
     */
    public function scopeByLocation($query, string $locationCode)
    {
        return $query->where('location_code', $locationCode);
    }

    /**
     * Scope by zone.
     */
    public function scopeByZone($query, string $zone)
    {
        return $query->where('zone', $zone);
    }

    /**
     * Scope by condition.
     */
    public function scopeByCondition($query, string $condition)
    {
        return $query->where('condition', $condition);
    }

    /**
     * Scope for expired items.
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Scope for items expiring soon.
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>', now());
    }

    /**
     * Get full location address.
     */
    public function getFullLocationAttribute(): string
    {
        $parts = array_filter([
            $this->location_name,
            $this->aisle,
            $this->shelf,
            $this->bin,
        ]);
        
        return implode(' - ', $parts);
    }

    /**
     * Get stock status.
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->quantity_available <= 0) {
            return 'out_of_stock';
        } elseif ($this->quantity_available <= $this->min_stock_level) {
            return 'low_stock';
        } elseif ($this->max_stock_level && $this->quantity_available >= $this->max_stock_level) {
            return 'overstock';
        } else {
            return 'in_stock';
        }
    }

    /**
     * Check if item is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    /**
     * Check if item is expiring soon.
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date && 
               $this->expiry_date <= now()->addDays($days) && 
               $this->expiry_date > now();
    }

    /**
     * Check if stock is low.
     */
    public function isLowStock(): bool
    {
        return $this->quantity_available <= $this->min_stock_level;
    }

    /**
     * Check if out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity_available <= 0;
    }

    /**
     * Update available quantity.
     */
    public function updateAvailableQuantity(): void
    {
        $this->update([
            'quantity_available' => $this->quantity_on_hand - $this->quantity_reserved
        ]);
    }

    /**
     * Add stock.
     */
    public function addStock(float $quantity): void
    {
        $this->increment('quantity_on_hand', $quantity);
        $this->updateAvailableQuantity();
        $this->touch('last_movement_at');
        $this->increment('movement_count');
    }

    /**
     * Remove stock.
     */
    public function removeStock(float $quantity): void
    {
        $this->decrement('quantity_on_hand', $quantity);
        $this->updateAvailableQuantity();
        $this->touch('last_movement_at');
        $this->increment('movement_count');
    }

    /**
     * Reserve stock.
     */
    public function reserveStock(float $quantity): void
    {
        $this->increment('quantity_reserved', $quantity);
        $this->updateAvailableQuantity();
    }

    /**
     * Release reserved stock.
     */
    public function releaseReservedStock(float $quantity): void
    {
        $this->decrement('quantity_reserved', $quantity);
        $this->updateAvailableQuantity();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return MaterialInventoryFactory::new();
    }
}
