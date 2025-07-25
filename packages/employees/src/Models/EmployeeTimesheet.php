<?php

namespace Packages\Employees\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\User\Models\User;

class EmployeeTimesheet extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'employee_id',
        'work_date',
        'check_in_time',
        'check_out_time',
        'break_start_time',
        'break_end_time',
        'regular_hours',
        'overtime_hours',
        'break_hours',
        'total_hours',
        'status',
        'absence_reason',
        'is_approved',
        'approved_by',
        'approved_at',
        'check_in_location',
        'check_out_location',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'check_in_device',
        'check_out_device',
        'check_in_ip',
        'check_out_ip',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'work_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'break_start_time' => 'datetime',
        'break_end_time' => 'datetime',
        'approved_at' => 'datetime',
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'break_hours' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'check_in_latitude' => 'decimal:8',
        'check_in_longitude' => 'decimal:8',
        'check_out_latitude' => 'decimal:8',
        'check_out_longitude' => 'decimal:8',
        'is_approved' => 'boolean',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('work_date', [$startDate, $endDate]);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    // Helper methods
    public function calculateHours(): void
    {
        if ($this->check_in_time && $this->check_out_time) {
            $totalMinutes = $this->check_out_time->diffInMinutes($this->check_in_time);
            
            // Calculate break time
            $breakMinutes = 0;
            if ($this->break_start_time && $this->break_end_time) {
                $breakMinutes = $this->break_end_time->diffInMinutes($this->break_start_time);
            }
            
            $workMinutes = $totalMinutes - $breakMinutes;
            $this->total_hours = round($workMinutes / 60, 2);
            $this->break_hours = round($breakMinutes / 60, 2);
            
            // Calculate regular and overtime hours (assuming 8 hours is regular)
            $regularHoursLimit = 8;
            if ($this->total_hours <= $regularHoursLimit) {
                $this->regular_hours = $this->total_hours;
                $this->overtime_hours = 0;
            } else {
                $this->regular_hours = $regularHoursLimit;
                $this->overtime_hours = $this->total_hours - $regularHoursLimit;
            }
        }
    }

    public function isLate(): bool
    {
        // This would need to be compared with the employee's scheduled start time
        // For now, assuming 8:00 AM as standard start time
        return $this->check_in_time && $this->check_in_time->format('H:i') > '08:00';
    }

    public function isEarlyLeave(): bool
    {
        // This would need to be compared with the employee's scheduled end time
        // For now, assuming 5:00 PM as standard end time
        return $this->check_out_time && $this->check_out_time->format('H:i') < '17:00';
    }

    public function hasOvertime(): bool
    {
        return $this->overtime_hours > 0;
    }

    public function getWorkDurationAttribute(): string
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return 'N/A';
        }
        
        return $this->check_in_time->format('H:i') . ' - ' . $this->check_out_time->format('H:i');
    }
}
