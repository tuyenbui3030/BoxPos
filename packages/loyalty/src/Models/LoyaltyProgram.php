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
        'code',
        'name',
        'description',
        'type',
        'status',
        'auto_enrollment',
        'earn_rate',
        'redeem_rate',
        'min_points_to_redeem',
        'max_points_per_transaction',
        'points_expiry_days',
        'tier_config',
        'tier_based_earning',
        'tier_benefits',
        'cashback_rate',
        'min_cashback_amount',
        'max_cashback_amount',
        'visits_for_reward',
        'visit_reward_amount',
        'start_date',
        'end_date',
        'eligible_customer_groups',
        'min_purchase_amount',
        'excluded_products',
        'excluded_categories',
        'earning_channels',
        'redemption_channels',
        'bonus_events',
        'multiplier_rules',
        'birthday_bonus',
        'birthday_bonus_points',
        'welcome_bonus',
        'welcome_bonus_points',
        'referral_bonus',
        'referral_bonus_points',
        'notification_settings',
        'terms_conditions',
        'privacy_policy',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_enrollment' => 'boolean',
        'earn_rate' => 'decimal:4',
        'redeem_rate' => 'decimal:4',
        'tier_config' => 'array',
        'tier_based_earning' => 'boolean',
        'tier_benefits' => 'array',
        'cashback_rate' => 'decimal:2',
        'min_cashback_amount' => 'decimal:2',
        'max_cashback_amount' => 'decimal:2',
        'eligible_customer_groups' => 'array',
        'excluded_products' => 'array',
        'excluded_categories' => 'array',
        'earning_channels' => 'array',
        'redemption_channels' => 'array',
        'bonus_events' => 'array',
        'multiplier_rules' => 'array',
        'birthday_bonus' => 'boolean',
        'welcome_bonus' => 'boolean',
        'referral_bonus' => 'boolean',
        'notification_settings' => 'array',
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
