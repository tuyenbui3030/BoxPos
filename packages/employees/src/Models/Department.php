<?php

namespace Packages\Employees\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\User\Models\User;
use Packages\Employees\Database\Factories\DepartmentFactory;

class Department extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'parent_id',
        'code',
        'name',
        'description',
        'manager_id',
        'location',
        'budget',
        'max_employees',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootDepartments($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeByManager($query, int $managerId)
    {
        return $query->where('manager_id', $managerId);
    }

    // Helper methods
    public function getActiveEmployeesCountAttribute(): int
    {
        return $this->employees()->where('status', 'active')->count();
    }

    public function isAtCapacity(): bool
    {
        return $this->max_employees && $this->active_employees_count >= $this->max_employees;
    }

    public function getHierarchyLevelAttribute(): int
    {
        $level = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }
        
        return $level;
    }

    public function getAllChildrenIds(): array
    {
        $childrenIds = [];
        
        foreach ($this->children as $child) {
            $childrenIds[] = $child->id;
            $childrenIds = array_merge($childrenIds, $child->getAllChildrenIds());
        }
        
        return $childrenIds;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return DepartmentFactory::new();
    }
}
