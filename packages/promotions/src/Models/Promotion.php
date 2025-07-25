<?php

namespace Packages\Promotions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class Promotion extends Model
{
    protected $fillable = [
        'store_id',
        'promotion_code',
        'promotion_name',
        'promotion_type',
        'description',
        'start_date',
        'end_date',
        'is_active',
        'usage_limit',
        'usage_count',
        'min_order_amount',
        'max_discount_amount',
        'discount_type',
        'discount_value',
        'applicable_to',
        'applicable_items',
        'customer_eligibility',
        'terms_conditions',
        'priority',
        'stackable',
        'auto_apply',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'applicable_items' => 'array',
        'customer_eligibility' => 'array',
        'stackable' => 'boolean',
        'auto_apply' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the store that owns this promotion.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the user who created this promotion.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the promotion usages.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }

    /**
     * Scope to get active promotions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    /**
     * Scope to get promotions for a specific store.
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Scope to get promotions by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('promotion_type', $type);
    }

    /**
     * Check if promotion is currently active.
     */
    public function isCurrentlyActive(): bool
    {
        return $this->is_active 
            && $this->start_date <= now() 
            && $this->end_date >= now();
    }

    /**
     * Check if promotion has usage limit.
     */
    public function hasUsageLimit(): bool
    {
        return $this->usage_limit > 0;
    }

    /**
     * Check if promotion usage limit is reached.
     */
    public function isUsageLimitReached(): bool
    {
        return $this->hasUsageLimit() && $this->usage_count >= $this->usage_limit;
    }

    /**
     * Check if promotion can be used.
     */
    public function canBeUsed(): bool
    {
        return $this->isCurrentlyActive() && !$this->isUsageLimitReached();
    }

    /**
     * Calculate discount amount for given order amount.
     */
    public function calculateDiscount(float $orderAmount): float
    {
        if ($orderAmount < $this->min_order_amount) {
            return 0;
        }

        $discount = 0;

        if ($this->discount_type === 'percentage') {
            $discount = $orderAmount * ($this->discount_value / 100);
        } elseif ($this->discount_type === 'fixed') {
            $discount = $this->discount_value;
        }

        // Apply maximum discount limit if set
        if ($this->max_discount_amount > 0) {
            $discount = min($discount, $this->max_discount_amount);
        }

        return $discount;
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Check if promotion is applicable to specific items.
     */
    public function isApplicableToItems(array $itemIds): bool
    {
        if ($this->applicable_to === 'all') {
            return true;
        }

        if ($this->applicable_to === 'specific_items' && $this->applicable_items) {
            return !empty(array_intersect($itemIds, $this->applicable_items));
        }

        return false;
    }

    /**
     * Check if customer is eligible for this promotion.
     */
    public function isCustomerEligible($customerId): bool
    {
        if (!$this->customer_eligibility) {
            return true; // No restrictions
        }

        $eligibility = $this->customer_eligibility;

        if (isset($eligibility['customer_type']) && $eligibility['customer_type'] === 'specific') {
            return in_array($customerId, $eligibility['customer_ids'] ?? []);
        }

        return true;
    }
}
