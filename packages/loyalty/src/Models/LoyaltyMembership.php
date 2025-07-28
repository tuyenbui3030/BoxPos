<?php

namespace Packages\Loyalty\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class LoyaltyMembership extends Model
{
    protected $fillable = [
        'store_id',
        'program_id',
        'customer_id',
        'membership_number',
        'status',
        'enrolled_date',
        'last_activity_date',
        'expiry_date',
        'total_points_earned',
        'total_points_redeemed',
        'current_points_balance',
        'pending_points',
        'expired_points',
        'current_tier',
        'tier_points',
        'next_tier_points',
        'tier_achieved_date',
        'tier_expiry_date',
        'total_transactions',
        'total_spent',
        'average_transaction',
        'visits_count',
        'first_purchase_date',
        'last_purchase_date',
        'total_cashback_earned',
        'total_cashback_redeemed',
        'current_cashback_balance',
        'referrals_made',
        'referral_points_earned',
        'referred_by',
        'email_notifications',
        'sms_notifications',
        'push_notifications',
        'marketing_emails',
        'preferences',
        'metadata',
    ];

    protected $casts = [
        'enrolled_date' => 'date',
        'last_activity_date' => 'date',
        'expiry_date' => 'date',
        'tier_achieved_date' => 'date',
        'tier_expiry_date' => 'date',
        'first_purchase_date' => 'date',
        'last_purchase_date' => 'date',
        'total_points_earned' => 'integer',
        'total_points_redeemed' => 'integer',
        'current_points_balance' => 'integer',
        'pending_points' => 'integer',
        'expired_points' => 'integer',
        'tier_points' => 'integer',
        'next_tier_points' => 'integer',
        'total_transactions' => 'integer',
        'visits_count' => 'integer',
        'referrals_made' => 'integer',
        'referral_points_earned' => 'integer',
        'total_spent' => 'decimal:2',
        'average_transaction' => 'decimal:2',
        'total_cashback_earned' => 'decimal:2',
        'total_cashback_redeemed' => 'decimal:2',
        'current_cashback_balance' => 'decimal:2',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'marketing_emails' => 'boolean',
        'preferences' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the store that owns this membership.
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
        return $this->belongsTo(LoyaltyProgram::class, 'program_id');
    }

    /**
     * Get the loyalty program (alias).
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(LoyaltyProgram::class, 'program_id');
    }

    /**
     * Get the customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    /**
     * Get the user who created this membership.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the loyalty transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    /**
     * Scope to get active memberships.
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
     * Scope to get memberships for a specific store.
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Scope to get memberships for a specific customer.
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Check if membership is active.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expiry_date && $this->expiry_date < now()) {
            return false;
        }

        return true;
    }

    /**
     * Add points to membership.
     */
    public function addPoints(int $points, string $reason = null): void
    {
        $this->current_points += $points;
        $this->lifetime_points += $points;
        $this->last_activity_date = now();
        
        // Update tier if program has tier system
        if ($this->loyaltyProgram->tier_system_enabled) {
            $this->updateTier();
        }
        
        $this->save();

        // Create transaction record
        $this->transactions()->create([
            'store_id' => $this->store_id,
            'loyalty_program_id' => $this->loyalty_program_id,
            'customer_id' => $this->customer_id,
            'transaction_type' => 'earn',
            'points' => $points,
            'balance_after' => $this->current_points,
            'description' => $reason ?? 'Points earned',
            'transaction_date' => now(),
        ]);
    }

    /**
     * Redeem points from membership.
     */
    public function redeemPoints(int $points, string $reason = null): bool
    {
        if ($this->current_points < $points) {
            return false;
        }

        if (!$this->loyaltyProgram->canRedeemPoints($points)) {
            return false;
        }

        $this->current_points -= $points;
        $this->points_redeemed += $points;
        $this->last_activity_date = now();
        $this->save();

        // Create transaction record
        $this->transactions()->create([
            'store_id' => $this->store_id,
            'loyalty_program_id' => $this->loyalty_program_id,
            'customer_id' => $this->customer_id,
            'transaction_type' => 'redeem',
            'points' => -$points,
            'balance_after' => $this->current_points,
            'description' => $reason ?? 'Points redeemed',
            'transaction_date' => now(),
        ]);

        return true;
    }

    /**
     * Update customer tier based on lifetime points.
     */
    public function updateTier(): void
    {
        $tier = $this->loyaltyProgram->getCustomerTier($this->lifetime_points);
        
        if ($tier) {
            $this->current_tier = $tier['tier_name'];
            
            // Calculate progress to next tier
            $nextTierThreshold = $this->getNextTierThreshold();
            if ($nextTierThreshold) {
                $currentTierThreshold = $tier['points_required'];
                $progress = ($this->lifetime_points - $currentTierThreshold) / 
                           ($nextTierThreshold - $currentTierThreshold) * 100;
                $this->tier_progress = min(100, max(0, $progress));
            } else {
                $this->tier_progress = 100; // Max tier reached
            }
        }
    }

    /**
     * Get next tier threshold.
     */
    private function getNextTierThreshold(): ?int
    {
        if (!$this->loyaltyProgram->tier_thresholds) {
            return null;
        }

        foreach ($this->loyaltyProgram->tier_thresholds as $tierData) {
            if ($this->lifetime_points < $tierData['points_required']) {
                return $tierData['points_required'];
            }
        }

        return null; // Already at max tier
    }

    /**
     * Get available tier benefits.
     */
    public function getTierBenefits(): array
    {
        return $this->loyaltyProgram->getTierBenefits($this->lifetime_points);
    }

    /**
     * Check if membership is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    /**
     * Extend membership expiry.
     */
    public function extendExpiry(int $days): void
    {
        if ($this->expiry_date) {
            $this->expiry_date = $this->expiry_date->addDays($days);
        } else {
            $this->expiry_date = now()->addDays($days);
        }
        
        $this->save();
    }
}
