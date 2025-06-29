<?php

namespace Packages\User\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Packages\User\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_login_at',
        'last_login_ip',
        'current_store_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the user's devices.
     */
    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    /**
     * Get active devices (used within last 30 days).
     */
    public function activeDevices(): HasMany
    {
        return $this->devices()->active();
    }

    /**
     * Get trusted devices.
     */
    public function trustedDevices(): HasMany
    {
        return $this->devices()->trusted();
    }

    /**
     * Get the user's current store.
     */
    public function currentStore(): BelongsTo
    {
        return $this->belongsTo(\Packages\Store\Models\Store::class, 'current_store_id');
    }

    /**
     * Get the stores that this user belongs to.
     */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(\Packages\Store\Models\Store::class, 'user_stores')
            ->withPivot(['role', 'permissions', 'is_active', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Get active stores for this user.
     */
    public function activeStores(): BelongsToMany
    {
        return $this->stores()->wherePivot('is_active', true);
    }

    /**
     * Get stores where user has specific role.
     */
    public function storesByRole(string $role): BelongsToMany
    {
        return $this->stores()->wherePivot('role', $role);
    }

    /**
     * Get stores where user is admin.
     */
    public function adminStores(): BelongsToMany
    {
        return $this->storesByRole('admin');
    }

    /**
     * Get stores where user is manager.
     */
    public function managerStores(): BelongsToMany
    {
        return $this->storesByRole('manager');
    }

    /**
     * Check if user has access to a specific store.
     */
    public function hasAccessToStore(int $storeId): bool
    {
        return $this->stores()
            ->wherePivot('store_id', $storeId)
            ->wherePivot('is_active', true)
            ->exists();
    }

    /**
     * Check if user has permission in a specific store.
     */
    public function hasPermissionInStore(int $storeId, string $permission): bool
    {
        $userStore = $this->stores()
            ->wherePivot('store_id', $storeId)
            ->wherePivot('is_active', true)
            ->first();

        if (!$userStore) {
            return false;
        }

        $pivot = $userStore->pivot;

        // Admin has all permissions
        if ($pivot->role === 'admin') {
            return true;
        }

        $permissions = $pivot->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Get user's role in a specific store.
     */
    public function getRoleInStore(int $storeId): ?string
    {
        $userStore = $this->stores()
            ->wherePivot('store_id', $storeId)
            ->wherePivot('is_active', true)
            ->first();

        return $userStore?->pivot->role;
    }

    /**
     * Check if user is admin in a specific store.
     */
    public function isAdminInStore(int $storeId): bool
    {
        return $this->getRoleInStore($storeId) === 'admin';
    }

    /**
     * Check if user is manager in a specific store.
     */
    public function isManagerInStore(int $storeId): bool
    {
        return in_array($this->getRoleInStore($storeId), ['admin', 'manager']);
    }

    /**
     * Check if user has current store set.
     */
    public function hasCurrentStore(): bool
    {
        return $this->current_store_id !== null;
    }

    /**
     * Set current store for user.
     */
    public function setCurrentStore(int $storeId): void
    {
        if (!$this->hasAccessToStore($storeId)) {
            throw new \Exception("User does not have access to store {$storeId}");
        }

        $this->update(['current_store_id' => $storeId]);
    }

    /**
     * Clear current store for user.
     */
    public function clearCurrentStore(): void
    {
        $this->update(['current_store_id' => null]);
    }

    /**
     * Get user's accessible stores count.
     */
    public function getAccessibleStoresCountAttribute(): int
    {
        return $this->activeStores()->count();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Packages\User\Database\Factories\UserFactory::new();
    }
}
