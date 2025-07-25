<?php

namespace Packages\Employees\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\User\Models\User;

class EmployeeCommission extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'employee_id',
        'commission_period',
        'period_start_date',
        'period_end_date',
        'total_sales',
        'target_sales',
        'achievement_percentage',
        'total_orders',
        'target_orders',
        'base_commission_rate',
        'bonus_commission_rate',
        'base_commission_amount',
        'bonus_commission_amount',
        'total_commission',
        'deductions',
        'adjustments',
        'deduction_reason',
        'adjustment_reason',
        'net_commission',
        'payment_status',
        'payment_date',
        'payment_method',
        'payment_reference',
        'is_approved',
        'approved_by',
        'approved_at',
        'approval_notes',
        'sales_breakdown',
        'notes',
        'metadata',
        'calculated_by',
    ];

    protected $casts = [
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'total_sales' => 'decimal:2',
        'target_sales' => 'decimal:2',
        'achievement_percentage' => 'decimal:2',
        'base_commission_rate' => 'decimal:2',
        'bonus_commission_rate' => 'decimal:2',
        'base_commission_amount' => 'decimal:2',
        'bonus_commission_amount' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'deductions' => 'decimal:2',
        'adjustments' => 'decimal:2',
        'net_commission' => 'decimal:2',
        'is_approved' => 'boolean',
        'sales_breakdown' => 'array',
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

    public function calculator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    // Scopes
    public function scopeByPeriod($query, string $period)
    {
        return $query->where('commission_period', $period);
    }

    public function scopeByPaymentStatus($query, string $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('period_start_date', [$startDate, $endDate]);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopePendingPayment($query)
    {
        return $query->where('payment_status', 'pending')
                    ->where('is_approved', true);
    }

    // Helper methods
    public function calculateCommission(): void
    {
        // Calculate achievement percentage
        if ($this->target_sales > 0) {
            $this->achievement_percentage = ($this->total_sales / $this->target_sales) * 100;
        }

        // Calculate base commission
        $this->base_commission_amount = $this->total_sales * ($this->base_commission_rate / 100);

        // Calculate bonus commission if target is exceeded
        if ($this->achievement_percentage > 100) {
            $excessSales = $this->total_sales - $this->target_sales;
            $this->bonus_commission_amount = $excessSales * ($this->bonus_commission_rate / 100);
        } else {
            $this->bonus_commission_amount = 0;
        }

        // Calculate total commission
        $this->total_commission = $this->base_commission_amount + $this->bonus_commission_amount;

        // Calculate net commission after deductions and adjustments
        $this->net_commission = $this->total_commission - $this->deductions + $this->adjustments;
    }

    public function hasMetTarget(): bool
    {
        return $this->achievement_percentage >= 100;
    }

    public function hasExceededTarget(): bool
    {
        return $this->achievement_percentage > 100;
    }

    public function canBeApproved(): bool
    {
        return !$this->is_approved && $this->payment_status === 'pending';
    }

    public function canBePaid(): bool
    {
        return $this->is_approved && $this->payment_status === 'approved';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function getFormattedPeriodAttribute(): string
    {
        return $this->period_start_date->format('M Y') . ' - ' . $this->period_end_date->format('M Y');
    }

    public function getTargetAchievementStatusAttribute(): string
    {
        if ($this->achievement_percentage >= 100) {
            return 'Achieved';
        } elseif ($this->achievement_percentage >= 80) {
            return 'Near Target';
        } else {
            return 'Below Target';
        }
    }
}
