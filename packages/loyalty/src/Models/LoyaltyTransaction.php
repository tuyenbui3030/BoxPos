<?php

namespace Packages\Loyalty\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class LoyaltyTransaction extends Model
{
    protected $fillable = [
        'store_id',
        'loyalty_program_id',
        'loyalty_membership_id',
        'customer_id',
        'transaction_type',
        'transaction_date',
        'points',
        'balance_before',
        'balance_after',
        'order_number',
        'order_id',
        'order_type',
        'order_amount',
        'description',
        'reference_type',
        'reference_id',
        'expiry_date',
        'status',
        'processed_by',
        'processed_at',
        'notes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'expiry_date' => 'datetime',
        'processed_at' => 'datetime',
        'points' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'order_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the store that owns this transaction.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the loyalty program.
     */
    public function loyaltyProgram(): BelongsTo
    {
        return $this->belongsTo(LoyaltyProgram::class);
    }

    /**
     * Get the loyalty membership.
     */
    public function loyaltyMembership(): BelongsTo
    {
        return $this->belongsTo(LoyaltyMembership::class);
    }

    /**
     * Get the customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    /**
     * Get the user who processed this transaction.
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the user who created this transaction.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get transactions by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope to get earn transactions.
     */
    public function scopeEarned($query)
    {
        return $query->where('transaction_type', 'earn');
    }

    /**
     * Scope to get redeem transactions.
     */
    public function scopeRedeemed($query)
    {
        return $query->where('transaction_type', 'redeem');
    }

    /**
     * Scope to get transactions for a specific store.
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Scope to get transactions for a specific customer.
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope to get transactions within date range.
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get active (non-expired) transactions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now());
            });
    }

    /**
     * Check if transaction is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    /**
     * Check if transaction is an earning transaction.
     */
    public function isEarning(): bool
    {
        return $this->transaction_type === 'earn';
    }

    /**
     * Check if transaction is a redemption transaction.
     */
    public function isRedemption(): bool
    {
        return $this->transaction_type === 'redeem';
    }

    /**
     * Get absolute points value.
     */
    public function getAbsolutePoints(): int
    {
        return abs($this->points);
    }

    /**
     * Get transaction type display name.
     */
    public function getTypeDisplayName(): string
    {
        return match($this->transaction_type) {
            'earn' => 'Tích điểm',
            'redeem' => 'Đổi điểm',
            'expire' => 'Hết hạn',
            'adjust' => 'Điều chỉnh',
            'bonus' => 'Thưởng',
            'refund' => 'Hoàn điểm',
            default => 'Khác',
        };
    }

    /**
     * Get formatted points with sign.
     */
    public function getFormattedPoints(): string
    {
        $sign = $this->points >= 0 ? '+' : '';
        return $sign . number_format($this->points);
    }

    /**
     * Mark transaction as expired.
     */
    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
            'processed_at' => now(),
        ]);
    }

    /**
     * Get related order information.
     */
    public function getOrderInfo(): ?array
    {
        if (!$this->order_number) {
            return null;
        }

        return [
            'number' => $this->order_number,
            'id' => $this->order_id,
            'type' => $this->order_type,
            'amount' => $this->order_amount,
        ];
    }
}
