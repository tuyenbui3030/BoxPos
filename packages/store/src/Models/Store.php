<?php

namespace Packages\Store\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Builders\StoreBuilder;
use Packages\User\Models\User;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'logo',
        'settings',
        'status',
        'description',
        'address',
        'phone',
        'email',
        'timezone',
        'currency',
        'language',
    ];

    protected $casts = [
        'settings' => 'array',
        'status' => 'string',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Create a new Eloquent query builder for the model.
     */
    public function newEloquentBuilder($query)
    {
        return new StoreBuilder($query);
    }

    /**
     * Get the users that belong to this store.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_stores')
            ->withPivot(['role', 'permissions', 'is_active', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Get active users for this store.
     */
    public function activeUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('is_active', true);
    }

    /**
     * Get users with specific role for this store.
     */
    public function usersByRole(string $role): BelongsToMany
    {
        return $this->users()->wherePivot('role', $role);
    }

    /**
     * Get store admins.
     */
    public function admins(): BelongsToMany
    {
        return $this->usersByRole('admin');
    }

    /**
     * Get store managers.
     */
    public function managers(): BelongsToMany
    {
        return $this->usersByRole('manager');
    }

    /**
     * Get store staff.
     */
    public function staff(): BelongsToMany
    {
        return $this->usersByRole('staff');
    }

    /**
     * Scope for active stores.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for inactive stores.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Scope for suspended stores.
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', self::STATUS_SUSPENDED);
    }

    /**
     * Scope to search stores by name or slug.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('slug', 'like', "%{$term}%")
              ->orWhere('domain', 'like', "%{$term}%");
        });
    }

    /**
     * Check if store is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if store is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Check if store is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Get store setting by key.
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set store setting.
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
    }

    /**
     * Get formatted address.
     */
    public function getFormattedAddressAttribute(): string
    {
        return $this->address ?? 'No address provided';
    }

    /**
     * Get store logo URL.
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        
        return asset('images/default-store-logo.png');
    }

    /**
     * Get store URL.
     */
    public function getUrlAttribute(): string
    {
        if ($this->domain) {
            return "https://{$this->domain}";
        }
        
        return route('store.show', $this->slug);
    }

    /**
     * Generate unique slug for store.
     */
    public static function generateSlug(string $name): string
    {
        $baseSlug = \Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Packages\Store\Database\Factories\StoreFactory::new();
    }
}
