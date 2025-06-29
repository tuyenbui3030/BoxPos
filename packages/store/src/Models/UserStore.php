<?php

namespace Packages\Store\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\User\Models\User;

class UserStore extends Model
{
    use HasFactory;

    protected $table = 'user_stores';

    protected $fillable = [
        'user_id',
        'store_id',
        'role',
        'permissions',
        'is_active',
        'joined_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'joined_at' => 'datetime',
    ];

    // Available roles
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_STAFF = 'staff';
    const ROLE_VIEWER = 'viewer';

    // Available permissions
    const PERMISSION_VIEW_DASHBOARD = 'view_dashboard';
    const PERMISSION_MANAGE_CUSTOMERS = 'manage_customers';
    const PERMISSION_MANAGE_PRODUCTS = 'manage_products';
    const PERMISSION_MANAGE_ORDERS = 'manage_orders';
    const PERMISSION_MANAGE_INVENTORY = 'manage_inventory';
    const PERMISSION_MANAGE_REPORTS = 'manage_reports';
    const PERMISSION_MANAGE_SETTINGS = 'manage_settings';
    const PERMISSION_MANAGE_USERS = 'manage_users';

    /**
     * Get the user that belongs to this store.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the store that this user belongs to.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Scope for active user-store relationships.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inactive user-store relationships.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope by role.
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope for admins.
     */
    public function scopeAdmins($query)
    {
        return $query->byRole(self::ROLE_ADMIN);
    }

    /**
     * Scope for managers.
     */
    public function scopeManagers($query)
    {
        return $query->byRole(self::ROLE_MANAGER);
    }

    /**
     * Scope for staff.
     */
    public function scopeStaff($query)
    {
        return $query->byRole(self::ROLE_STAFF);
    }

    /**
     * Check if user has specific permission in this store.
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Admin has all permissions
        if ($this->role === self::ROLE_ADMIN) {
            return true;
        }

        $permissions = $this->permissions ?? [];

        // Handle JSON string permissions
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?? [];
        }

        return in_array($permission, $permissions);
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Grant permission to user in this store.
     */
    public function grantPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];

        // Handle JSON string permissions
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?? [];
        }

        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }
    }

    /**
     * Revoke permission from user in this store.
     */
    public function revokePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];

        // Handle JSON string permissions
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?? [];
        }

        $permissions = array_filter($permissions, fn($p) => $p !== $permission);
        $this->permissions = array_values($permissions);
        $this->save();
    }

    /**
     * Get default permissions for role.
     */
    public static function getDefaultPermissions(string $role): array
    {
        return match ($role) {
            self::ROLE_ADMIN => [
                self::PERMISSION_VIEW_DASHBOARD,
                self::PERMISSION_MANAGE_CUSTOMERS,
                self::PERMISSION_MANAGE_PRODUCTS,
                self::PERMISSION_MANAGE_ORDERS,
                self::PERMISSION_MANAGE_INVENTORY,
                self::PERMISSION_MANAGE_REPORTS,
                self::PERMISSION_MANAGE_SETTINGS,
                self::PERMISSION_MANAGE_USERS,
            ],
            self::ROLE_MANAGER => [
                self::PERMISSION_VIEW_DASHBOARD,
                self::PERMISSION_MANAGE_CUSTOMERS,
                self::PERMISSION_MANAGE_PRODUCTS,
                self::PERMISSION_MANAGE_ORDERS,
                self::PERMISSION_MANAGE_INVENTORY,
                self::PERMISSION_MANAGE_REPORTS,
            ],
            self::ROLE_STAFF => [
                self::PERMISSION_VIEW_DASHBOARD,
                self::PERMISSION_MANAGE_CUSTOMERS,
                self::PERMISSION_MANAGE_ORDERS,
            ],
            self::ROLE_VIEWER => [
                self::PERMISSION_VIEW_DASHBOARD,
            ],
            default => [],
        };
    }

    /**
     * Get all available roles.
     */
    public static function getAvailableRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_MANAGER,
            self::ROLE_STAFF,
            self::ROLE_VIEWER,
        ];
    }

    /**
     * Get all available permissions.
     */
    public static function getAvailablePermissions(): array
    {
        return [
            self::PERMISSION_VIEW_DASHBOARD,
            self::PERMISSION_MANAGE_CUSTOMERS,
            self::PERMISSION_MANAGE_PRODUCTS,
            self::PERMISSION_MANAGE_ORDERS,
            self::PERMISSION_MANAGE_INVENTORY,
            self::PERMISSION_MANAGE_REPORTS,
            self::PERMISSION_MANAGE_SETTINGS,
            self::PERMISSION_MANAGE_USERS,
        ];
    }

    /**
     * Check if user is admin in this store.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN && $this->is_active;
    }

    /**
     * Check if user is manager in this store.
     */
    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER && $this->is_active;
    }

    /**
     * Check if user is staff in this store.
     */
    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF && $this->is_active;
    }

    /**
     * Check if user is viewer in this store.
     */
    public function isViewer(): bool
    {
        return $this->role === self::ROLE_VIEWER && $this->is_active;
    }
}
