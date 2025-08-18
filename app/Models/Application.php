<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color_scheme',
        'available_packages',
        'pricing_tiers',
        'features',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'color_scheme' => 'array',
        'available_packages' => 'array',
        'pricing_tiers' => 'array',
        'features' => 'array',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_BETA = 'beta';
    const STATUS_COMING_SOON = 'coming_soon';
    const STATUS_DEPRECATED = 'deprecated';

    /**
     * Get subscriptions for this application
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get active subscriptions for this application
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->subscriptions()->where('status', 'active');
    }

    /**
     * Get stores using this application
     */
    public function stores(): HasMany
    {
        return $this->hasMany(\Packages\Store\Models\Store::class);
    }

    /**
     * Scope for active applications
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for beta applications
     */
    public function scopeBeta($query)
    {
        return $query->where('status', self::STATUS_BETA);
    }

    /**
     * Scope ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Check if application is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if application is in beta
     */
    public function isBeta(): bool
    {
        return $this->status === self::STATUS_BETA;
    }

    /**
     * Get pricing for specific plan
     */
    public function getPricing(string $planType): ?array
    {
        return $this->pricing_tiers[$planType] ?? null;
    }

    /**
     * Get available features for specific plan
     */
    public function getFeaturesForPlan(string $planType): array
    {
        $pricing = $this->getPricing($planType);
        return $pricing['features'] ?? [];
    }

    /**
     * Get monthly price for specific plan
     */
    public function getMonthlyPrice(string $planType): float
    {
        $pricing = $this->getPricing($planType);
        return $pricing['monthly_price'] ?? 0;
    }

    /**
     * Get max stores for specific plan
     */
    public function getMaxStores(string $planType): int
    {
        $pricing = $this->getPricing($planType);
        return $pricing['max_stores'] ?? 1;
    }

    /**
     * Get primary color from color scheme
     */
    public function getPrimaryColor(): string
    {
        return $this->color_scheme['primary'] ?? '#3B82F6';
    }

    /**
     * Get secondary color from color scheme
     */
    public function getSecondaryColor(): string
    {
        return $this->color_scheme['secondary'] ?? '#64748B';
    }

    /**
     * Get icon URL
     */
    public function getIconUrl(): string
    {
        if ($this->icon) {
            return asset('storage/' . $this->icon);
        }
        
        return asset('images/apps/default-app-icon.png');
    }
}