<?php

namespace Packages\Promotions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class PromotionUsage extends Model
{
    protected $fillable = [
        'store_id',
        'promotion_id',
        'customer_id',
        'order_type',
        'order_id',
        'order_number',
        'usage_date',
        'discount_amount',
        'order_amount',
        'usage_context',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'usage_date' => 'datetime',
        'discount_amount' => 'decimal:2',
        'order_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the store that owns this usage.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the promotion that was used.
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Get the customer who used the promotion.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    /**
     * Get the user who created this usage record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get usages for a specific store.
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Scope to get usages for a specific promotion.
     */
    public function scopeForPromotion($query, $promotionId)
    {
        return $query->where('promotion_id', $promotionId);
    }

    /**
     * Scope to get usages for a specific customer.
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope to get usages within date range.
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('usage_date', [$startDate, $endDate]);
    }

    /**
     * Get total discount amount for a promotion.
     */
    public static function getTotalDiscountForPromotion($promotionId): float
    {
        return static::where('promotion_id', $promotionId)->sum('discount_amount');
    }

    /**
     * Get usage count for a promotion.
     */
    public static function getUsageCountForPromotion($promotionId): int
    {
        return static::where('promotion_id', $promotionId)->count();
    }

    /**
     * Get usage count for a customer and promotion.
     */
    public static function getCustomerUsageCount($customerId, $promotionId): int
    {
        return static::where('customer_id', $customerId)
            ->where('promotion_id', $promotionId)
            ->count();
    }
}
