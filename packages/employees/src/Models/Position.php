<?php

namespace Packages\Employees\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\Employees\Database\Factories\PositionFactory;

class Position extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'department_id',
        'code',
        'title',
        'description',
        'responsibilities',
        'requirements',
        'level',
        'min_salary',
        'max_salary',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    public function scopeBySalaryRange($query, float $minSalary, float $maxSalary)
    {
        return $query->where('min_salary', '>=', $minSalary)
                    ->where('max_salary', '<=', $maxSalary);
    }

    // Helper methods
    public function getActiveEmployeesCountAttribute(): int
    {
        return $this->employees()->where('status', 'active')->count();
    }

    public function getSalaryRangeAttribute(): string
    {
        return number_format($this->min_salary, 0) . ' - ' . number_format($this->max_salary, 0) . ' VND';
    }

    public function isManagementLevel(): bool
    {
        return in_array($this->level, ['manager', 'director', 'executive']);
    }

    public function isSeniorLevel(): bool
    {
        return in_array($this->level, ['senior', 'lead', 'manager', 'director', 'executive']);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return PositionFactory::new();
    }
}
