<?php

namespace Packages\MaterialPricing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Packages\Tenant\Traits\HasTenantScope;

class MaterialPricing extends Model
{
    use HasFactory, HasTenantScope;

    protected $table = 'material_pricing';

    protected $fillable = [
        'store_id',
        'material_id',
        'price_list_name',
        'customer_type',
        'min_quantity',
        'max_quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'effective_from',
        'effective_to',
        'season',
        'delivery_areas',
        'delivery_surcharge',
        'free_delivery',
        'free_delivery_threshold',
        'currency',
        'tax_rate',
        'tax_inclusive',
        'is_active',
        'is_default',
        'priority',
        'notes',
        'conditions',
        'created_by',
    ];

    protected $casts = [
        'min_quantity' => 'decimal:3',
        'max_quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'delivery_surcharge' => 'decimal:2',
        'free_delivery_threshold' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'delivery_areas' => 'array',
        'conditions' => 'array',
        'free_delivery' => 'boolean',
        'tax_inclusive' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'priority' => 'integer',
    ];

    const CUSTOMER_RETAIL = 'retail';
    const CUSTOMER_WHOLESALE = 'wholesale';
    const CUSTOMER_CONTRACTOR = 'contractor';
    const CUSTOMER_VIP = 'vip';
    const CUSTOMER_STAFF = 'staff';

    const SEASON_ALL_YEAR = 'all_year';
    const SEASON_DRY = 'dry_season';
    const SEASON_RAINY = 'rainy_season';
    const SEASON_PEAK = 'peak_season';
    const SEASON_OFF = 'off_season';

    /**
     * Get the store that owns this pricing.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the material for this pricing.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(BuildingMaterial::class, 'material_id');
    }

    /**
     * Get the user who created this pricing.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active pricing.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default pricing.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope by customer type.
     */
    public function scopeByCustomerType($query, string $customerType)
    {
        return $query->where('customer_type', $customerType);
    }

    /**
     * Scope by season.
     */
    public function scopeBySeason($query, string $season)
    {
        return $query->where('season', $season);
    }

    /**
     * Scope for current effective pricing.
     */
    public function scopeCurrentlyEffective($query)
    {
        $today = now()->toDateString();
        return $query->where('effective_from', '<=', $today)
                    ->where(function($q) use ($today) {
                        $q->whereNull('effective_to')
                          ->orWhere('effective_to', '>=', $today);
                    });
    }

    /**
     * Scope for quantity range.
     */
    public function scopeForQuantity($query, float $quantity)
    {
        return $query->where('min_quantity', '<=', $quantity)
                    ->where(function($q) use ($quantity) {
                        $q->whereNull('max_quantity')
                          ->orWhere('max_quantity', '>=', $quantity);
                    });
    }

    /**
     * Get effective price after discounts.
     */
    public function getEffectivePriceAttribute(): float
    {
        $price = $this->unit_price;
        
        if ($this->discount_percentage > 0) {
            $price = $price * (1 - $this->discount_percentage / 100);
        }
        
        if ($this->discount_amount > 0) {
            $price = $price - $this->discount_amount;
        }
        
        return max(0, $price);
    }

    /**
     * Get price including tax.
     */
    public function getPriceIncludingTaxAttribute(): float
    {
        $price = $this->effective_price;
        
        if (!$this->tax_inclusive && $this->tax_rate > 0) {
            $price = $price * (1 + $this->tax_rate / 100);
        }
        
        return $price;
    }

    /**
     * Get total savings amount.
     */
    public function getTotalSavingsAttribute(): float
    {
        $savings = 0;
        
        if ($this->discount_percentage > 0) {
            $savings += $this->unit_price * ($this->discount_percentage / 100);
        }
        
        if ($this->discount_amount > 0) {
            $savings += $this->discount_amount;
        }
        
        return $savings;
    }

    /**
     * Check if pricing is currently effective.
     */
    public function isCurrentlyEffective(): bool
    {
        $today = now()->toDateString();
        
        return $this->effective_from <= $today && 
               (is_null($this->effective_to) || $this->effective_to >= $today);
    }

    /**
     * Check if quantity qualifies for this pricing.
     */
    public function qualifiesForQuantity(float $quantity): bool
    {
        return $quantity >= $this->min_quantity && 
               (is_null($this->max_quantity) || $quantity <= $this->max_quantity);
    }

    /**
     * Check if delivery area is covered.
     */
    public function coversDeliveryArea(string $area): bool
    {
        if (empty($this->delivery_areas)) {
            return true; // No restriction
        }
        
        return in_array($area, $this->delivery_areas);
    }

    /**
     * Check if order qualifies for free delivery.
     */
    public function qualifiesForFreeDelivery(float $orderValue): bool
    {
        return $this->free_delivery || 
               ($this->free_delivery_threshold && $orderValue >= $this->free_delivery_threshold);
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->effective_price, 0, ',', '.') . ' ' . $this->currency;
    }

    /**
     * Get formatted price range.
     */
    public function getFormattedPriceRangeAttribute(): string
    {
        $range = '';
        
        if ($this->min_quantity > 1) {
            $range .= "Từ {$this->min_quantity}";
        }
        
        if ($this->max_quantity) {
            $range .= $range ? " đến {$this->max_quantity}" : "Tối đa {$this->max_quantity}";
        } else {
            $range .= $range ? '+' : 'Không giới hạn';
        }
        
        return $range . ' đơn vị';
    }

    /**
     * Find best pricing for material and criteria.
     */
    public static function findBestPricing(int $materialId, string $customerType, float $quantity, ?string $season = null): ?self
    {
        return static::where('material_id', $materialId)
                    ->active()
                    ->currentlyEffective()
                    ->byCustomerType($customerType)
                    ->forQuantity($quantity)
                    ->when($season, fn($q) => $q->where(function($query) use ($season) {
                        $query->bySeason($season)->orWhere('season', self::SEASON_ALL_YEAR);
                    }))
                    ->orderBy('priority', 'desc')
                    ->orderBy('effective_price', 'asc')
                    ->first();
    }
}
