<?php

namespace Packages\Employees\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\User\Models\User;

class EmployeeSchedule extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'employee_id',
        'schedule_date',
        'shift_name',
        'start_time',
        'end_time',
        'break_start_time',
        'break_end_time',
        'scheduled_hours',
        'status',
        'schedule_type',
        'work_location',
        'department_id',
        'role_for_shift',
        'is_available',
        'unavailable_reason',
        'has_conflict',
        'conflict_details',
        'is_approved',
        'approved_by',
        'approved_at',
        'original_employee_id',
        'is_swap_request',
        'swap_status',
        'swap_approved_by',
        'swap_approved_at',
        'notes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'break_start_time' => 'datetime',
        'break_end_time' => 'datetime',
        'approved_at' => 'datetime',
        'swap_approved_at' => 'datetime',
        'scheduled_hours' => 'decimal:2',
        'is_available' => 'boolean',
        'has_conflict' => 'boolean',
        'is_approved' => 'boolean',
        'is_swap_request' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function swapApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'swap_approved_by');
    }

    public function originalEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'original_employee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByScheduleType($query, string $type)
    {
        return $query->where('schedule_type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('schedule_date', [$startDate, $endDate]);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeSwapRequests($query)
    {
        return $query->where('is_swap_request', true);
    }

    public function scopePendingSwapApproval($query)
    {
        return $query->where('is_swap_request', true)
                    ->where('swap_status', 'pending');
    }

    // Helper methods
    public function calculateScheduledHours(): void
    {
        if ($this->start_time && $this->end_time) {
            $totalMinutes = $this->end_time->diffInMinutes($this->start_time);
            
            // Calculate break time
            $breakMinutes = 0;
            if ($this->break_start_time && $this->break_end_time) {
                $breakMinutes = $this->break_end_time->diffInMinutes($this->break_start_time);
            }
            
            $workMinutes = $totalMinutes - $breakMinutes;
            $this->scheduled_hours = round($workMinutes / 60, 2);
        }
    }

    public function isOvertime(): bool
    {
        return $this->schedule_type === 'overtime' || $this->scheduled_hours > 8;
    }

    public function isHoliday(): bool
    {
        return $this->schedule_type === 'holiday';
    }

    public function canBeSwapped(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed']) && 
               !$this->is_swap_request && 
               $this->schedule_date->isFuture();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed']) && 
               $this->schedule_date->isFuture();
    }

    public function getShiftDurationAttribute(): string
    {
        if (!$this->start_time || !$this->end_time) {
            return 'N/A';
        }
        
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }

    public function hasConflict(): bool
    {
        return $this->has_conflict;
    }

    public function isSwapRequest(): bool
    {
        return $this->is_swap_request;
    }
}
