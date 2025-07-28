<?php

namespace Packages\Employees\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\User\Models\User;
use Packages\Employees\Database\Factories\EmployeeFactory;

class Employee extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'user_id',
        'department_id',
        'position_id',
        'manager_id',
        'employee_code',
        'full_name',
        'first_name',
        'last_name',
        'id_number',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'hire_date',
        'probation_end_date',
        'employment_type',
        'contract_type',
        'contract_start_date',
        'contract_end_date',
        'work_location',
        'basic_salary',
        'salary_grade',
        'pay_frequency',
        'currency',
        'salary_effective_date',
        'working_hours',
        'weekly_hours',
        'break_time',
        'health_insurance',
        'social_insurance',
        'vacation_days',
        'sick_leave_days',
        'tax_id',
        'dependents',
        'status',
        'termination_date',
        'termination_reason',
        'notes',
        'skills',
        'certifications',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'probation_end_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'salary_effective_date' => 'date',
        'termination_date' => 'date',
        'basic_salary' => 'decimal:2',
        'health_insurance' => 'boolean',
        'social_insurance' => 'boolean',
        'working_hours' => 'array',
        'break_time' => 'array',
        'skills' => 'array',
        'certifications' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(EmployeeTimesheet::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(EmployeeCommission::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(EmployeePayroll::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByPosition($query, int $positionId)
    {
        return $query->where('position_id', $positionId);
    }

    public function scopeByEmploymentType($query, string $type)
    {
        return $query->where('employment_type', $type);
    }

    public function scopeHiredBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('hire_date', [$startDate, $endDate]);
    }

    // Helper methods
    public static function generateEmployeeCode(int $storeId): string
    {
        $lastEmployee = static::where('store_id', $storeId)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastEmployee ? ($lastEmployee->id + 1) : 1;
        return 'EMP' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function getDisplayNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name) ?: $this->full_name;
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getTenureAttribute(): ?int
    {
        return $this->hire_date ? $this->hire_date->diffInYears(now()) : null;
    }

    public function isOnProbation(): bool
    {
        return $this->probation_end_date && $this->probation_end_date->isFuture();
    }

    public function isContractExpiring(int $days = 30): bool
    {
        return $this->contract_end_date && 
               $this->contract_end_date->isFuture() && 
               $this->contract_end_date->diffInDays(now()) <= $days;
    }

    public function canManageEmployees(): bool
    {
        return $this->subordinates()->count() > 0;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return EmployeeFactory::new();
    }
}
