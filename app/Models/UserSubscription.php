<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'application_id',
        'plan_type',
        'status',
        'starts_at',
        'expires_at',
        'max_stores',
        'features',
        'monthly_price',
        'billing_info',
        'last_billed_at',
        'next_billing_at',
    ];

    protected $casts = [
        'features' => 'array',
        'billing_info' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_billed_at' => 'datetime',
        'next_billing_at' => 'datetime',
        'monthly_price' => 'decimal:2',
    ];

    const PLAN_TRIAL = 'trial';
    const PLAN_BASIC = 'basic';
    const PLAN_PROFESSIONAL = 'professional';
    const PLAN_ENTERPRISE = 'enterprise';

    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    /**
     * Get the user that owns this subscription
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Packages\User\Models\User::class);
    }

    /**
     * Get the application for this subscription
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get stores created under this subscription
     */
    public function stores(): HasMany
    {
        return $this->hasMany(\Packages\Store\Models\Store::class, 'subscription_id');
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope for expired subscriptions
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope for trial subscriptions
     */
    public function scopeTrial($query)
    {
        return $query->where('plan_type', self::PLAN_TRIAL);
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if subscription is trial
     */
    public function isTrial(): bool
    {
        return $this->plan_type === self::PLAN_TRIAL;
    }

    /**
     * Check if subscription is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Get days remaining in subscription
     */
    public function getDaysRemaining(): ?int
    {
        if (!$this->expires_at) {
            return null; // Unlimited
        }

        return max(0, now()->diffInDays($this->expires_at, false));
    }

    /**
     * Check if user has specific feature
     */
    public function hasFeature(string $feature): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $features = $this->features ?? [];
        return in_array($feature, $features) || in_array('*', $features);
    }

    /**
     * Check if user can create more stores
     */
    public function canCreateStore(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->max_stores === -1) {
            return true; // Unlimited
        }

        return $this->stores()->count() < $this->max_stores;
    }

    /**
     * Get remaining store slots
     */
    public function getRemainingStores(): int
    {
        if ($this->max_stores === -1) {
            return -1; // Unlimited
        }

        return max(0, $this->max_stores - $this->stores()->count());
    }

    /**
     * Extend subscription
     */
    public function extend(int $days): void
    {
        $expiresAt = $this->expires_at ?? now();
        $this->expires_at = Carbon::parse($expiresAt)->addDays($days);
        $this->save();
    }

    /**
     * Cancel subscription
     */
    public function cancel(): void
    {
        $this->status = self::STATUS_CANCELLED;
        $this->save();
    }

    /**
     * Suspend subscription
     */
    public function suspend(): void
    {
        $this->status = self::STATUS_SUSPENDED;
        $this->save();
    }

    /**
     * Reactivate subscription
     */
    public function reactivate(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    /**
     * Upgrade to new plan
     */
    public function upgradeTo(string $newPlan, array $newFeatures, int $newMaxStores, float $newPrice): void
    {
        $this->plan_type = $newPlan;
        $this->features = $newFeatures;
        $this->max_stores = $newMaxStores;
        $this->monthly_price = $newPrice;
        $this->save();
    }

    /**
     * Get available plans
     */
    public static function getAvailablePlans(): array
    {
        return [
            self::PLAN_TRIAL,
            self::PLAN_BASIC,
            self::PLAN_PROFESSIONAL,
            self::PLAN_ENTERPRISE,
        ];
    }

    /**
     * Get available statuses
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_SUSPENDED,
            self::STATUS_CANCELLED,
            self::STATUS_EXPIRED,
        ];
    }
}