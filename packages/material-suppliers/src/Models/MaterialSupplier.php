<?php

namespace Packages\MaterialSuppliers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\MaterialSuppliers\Builders\MaterialSupplierBuilder;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Packages\Tenant\Traits\HasTenantScope;

class MaterialSupplier extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'supplier_code',
        'company_name',
        'contact_person',
        'phone',
        'email',
        'website',
        'address',
        'city',
        'province',
        'postal_code',
        'country',
        'tax_code',
        'supplier_type',
        'payment_terms',
        'credit_limit',
        'current_balance',
        'lead_time_days',
        'rating',
        'is_active',
        'is_preferred',
        'certifications',
        'delivery_areas',
        'notes',
        'last_order_at',
        'created_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'lead_time_days' => 'integer',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
        'is_preferred' => 'boolean',
        'certifications' => 'array',
        'delivery_areas' => 'array',
        'last_order_at' => 'datetime',
    ];

    const TYPE_MANUFACTURER = 'manufacturer';
    const TYPE_DISTRIBUTOR = 'distributor';
    const TYPE_RETAILER = 'retailer';
    const TYPE_IMPORTER = 'importer';

    const PAYMENT_CASH = 'cash';
    const PAYMENT_COD = 'cod';
    const PAYMENT_NET_15 = 'net_15';
    const PAYMENT_NET_30 = 'net_30';
    const PAYMENT_NET_60 = 'net_60';
    const PAYMENT_NET_90 = 'net_90';

    /**
     * Create a new Eloquent query builder for the model.
     */
    public function newEloquentBuilder($query)
    {
        return new MaterialSupplierBuilder($query);
    }

    /**
     * Get the store that owns this supplier.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the user who created this supplier.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get contacts for this supplier.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class, 'supplier_id');
    }

    /**
     * Get active contacts for this supplier.
     */
    public function activeContacts(): HasMany
    {
        return $this->contacts()->where('is_active', true);
    }

    /**
     * Get primary contact for this supplier.
     */
    public function primaryContact()
    {
        return $this->contacts()->where('is_primary', true)->first();
    }

    /**
     * Scope for active suppliers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for preferred suppliers.
     */
    public function scopePreferred($query)
    {
        return $query->where('is_preferred', true);
    }

    /**
     * Scope by supplier type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('supplier_type', $type);
    }

    /**
     * Scope by payment terms.
     */
    public function scopeByPaymentTerms($query, string $terms)
    {
        return $query->where('payment_terms', $terms);
    }

    /**
     * Scope by rating range.
     */
    public function scopeByRating($query, float $minRating, ?float $maxRating = null)
    {
        $query = $query->where('rating', '>=', $minRating);
        
        if ($maxRating !== null) {
            $query = $query->where('rating', '<=', $maxRating);
        }
        
        return $query;
    }

    /**
     * Scope to search suppliers.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('supplier_code', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%")
              ->orWhere('contact_person', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Get available credit amount.
     */
    public function getAvailableCreditAttribute(): float
    {
        return max(0, $this->credit_limit - $this->current_balance);
    }

    /**
     * Get credit utilization percentage.
     */
    public function getCreditUtilizationAttribute(): float
    {
        if ($this->credit_limit <= 0) {
            return 0;
        }
        
        return ($this->current_balance / $this->credit_limit) * 100;
    }

    /**
     * Get formatted address.
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->province,
            $this->postal_code,
            $this->country,
        ]);
        
        return implode(', ', $parts);
    }

    /**
     * Check if supplier is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if supplier is preferred.
     */
    public function isPreferred(): bool
    {
        return $this->is_preferred;
    }

    /**
     * Check if supplier has good rating.
     */
    public function hasGoodRating(float $threshold = 4.0): bool
    {
        return $this->rating >= $threshold;
    }

    /**
     * Check if supplier has available credit.
     */
    public function hasAvailableCredit(float $amount = 0): bool
    {
        return $this->available_credit >= $amount;
    }

    /**
     * Generate unique supplier code.
     */
    public static function generateSupplierCode(int $storeId): string
    {
        $lastSupplier = static::where('store_id', $storeId)
                             ->orderBy('id', 'desc')
                             ->first();
        
        $number = $lastSupplier ? ($lastSupplier->id + 1) : 1;
        return 'SUP' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Packages\MaterialSuppliers\Database\Factories\MaterialSupplierFactory::new();
    }
}
