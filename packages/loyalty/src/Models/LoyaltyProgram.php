<?php

namespace Packages\Loyalty\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class LoyaltyProgram extends Model
{
    protected $fillable = [
        'store_id',
        'name',
        'code',
        'description',
        'type',
        'status',
        'start_date',
        'end_date',
        'points_per_currency',
        'currency_per_point',
        'min_points_redeem',
        'max_points_redeem',
        'point_expiry_days',
        'tier_system_enabled',
        'tier_thresholds',
        'tier_benefits',
        'signup_bonus_points',
        'referral_bonus_points',
        'birthday_bonus_points',
        'terms_conditions',
        'is_active',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'points_per_currency' => 'decimal:2',
        'currency_per_point' => 'decimal:2',
        'tier_system_enabled' => 'boolean',
        'tier_thresholds' => 'array',
        'tier_benefits' => 'array',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the store that owns this loyalty program.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the user who created this loyalty program.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the loyalty memberships.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(LoyaltyMembership::class);
    }

    /**
     * Get the loyalty transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    /**
     * Scope to get active programs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope to get programs for a specific store.
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Check if program is currently active.
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active || $this->status !== 'active') {
            return false;
        }

        $now = now();

        if ($this->start_date && $this->start_date > $now) {
            return false;
        }

        if ($this->end_date && $this->end_date < $now) {
            return false;
        }

        return true;
    }

    /**
     * Calculate points for a given amount.
     */
    public function calculatePoints(float $amount): int
    {
        if ($this->points_per_currency <= 0) {
            return 0;
        }

        return (int) floor($amount * $this->points_per_currency);
    }

    /**
     * Calculate currency value for given points.
     */
    public function calculateCurrencyValue(int $points): float
    {
        if ($this->currency_per_point <= 0) {
            return 0;
        }

        return $points * $this->currency_per_point;
    }

    /**
     * Check if points amount can be redeemed.
     */
    public function canRedeemPoints(int $points): bool
    {
        if ($points < $this->min_points_redeem) {
            return false;
        }

        if ($this->max_points_redeem > 0 && $points > $this->max_points_redeem) {
            return false;
        }

        return true;
    }

    /**
     * Get customer tier based on total points.
     */
    public function getCustomerTier(int $totalPoints): ?array
    {
        if (!$this->tier_system_enabled || !$this->tier_thresholds) {
            return null;
        }

        $tier = null;
        foreach ($this->tier_thresholds as $tierData) {
            if ($totalPoints >= $tierData['points_required']) {
                $tier = $tierData;
            } else {
                break;
            }
        }

        return $tier;
    }

    /**
     * Get tier benefits for a customer.
     */
    public function getTierBenefits(int $totalPoints): array
    {
        $tier = $this->getCustomerTier($totalPoints);
        
        if (!$tier || !$this->tier_benefits) {
            return [];
        }

        return $this->tier_benefits[$tier['tier_name']] ?? [];
    }

    /**
     * Check if points are expired.
     */
    public function arePointsExpired(\DateTime $earnedDate): bool
    {
        if ($this->point_expiry_days <= 0) {
            return false; // Points never expire
        }

        $expiryDate = clone $earnedDate;
        $expiryDate->modify("+{$this->point_expiry_days} days");

        return $expiryDate < now();
    }
}
