<?php

namespace Packages\Employees\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\User\Models\User;

class EmployeePayroll extends Model
{
    use HasFactory, HasTenantScope;

    protected $table = 'employee_payroll';

    protected $fillable = [
        'store_id',
        'employee_id',
        'payroll_period',
        'period_start_date',
        'period_end_date',
        'pay_date',
        'basic_salary',
        'hourly_rate',
        'regular_hours',
        'overtime_hours',
        'overtime_rate',
        'overtime_pay',
        'commission',
        'bonus',
        'allowances',
        'holiday_pay',
        'other_earnings',
        'earnings_breakdown',
        'gross_pay',
        'income_tax',
        'social_insurance',
        'health_insurance',
        'unemployment_insurance',
        'union_dues',
        'loan_deduction',
        'advance_deduction',
        'other_deductions',
        'deductions_breakdown',
        'total_deductions',
        'net_pay',
        'payment_status',
        'payment_method',
        'bank_account',
        'payment_reference',
        'paid_at',
        'is_approved',
        'approved_by',
        'approved_at',
        'approval_notes',
        'notes',
        'metadata',
        'calculated_by',
    ];

    protected $casts = [
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'pay_date' => 'date',
        'paid_at' => 'datetime',
        'approved_at' => 'datetime',
        'basic_salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'commission' => 'decimal:2',
        'bonus' => 'decimal:2',
        'allowances' => 'decimal:2',
        'holiday_pay' => 'decimal:2',
        'other_earnings' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'income_tax' => 'decimal:2',
        'social_insurance' => 'decimal:2',
        'health_insurance' => 'decimal:2',
        'unemployment_insurance' => 'decimal:2',
        'union_dues' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'is_approved' => 'boolean',
        'earnings_breakdown' => 'array',
        'deductions_breakdown' => 'array',
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
        return $query->where('payroll_period', $period);
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
    public function calculatePayroll(): void
    {
        // Calculate overtime pay
        $this->overtime_pay = $this->overtime_hours * $this->overtime_rate;

        // Calculate gross pay
        $this->gross_pay = $this->basic_salary + 
                          $this->overtime_pay + 
                          $this->commission + 
                          $this->bonus + 
                          $this->allowances + 
                          $this->holiday_pay + 
                          $this->other_earnings;

        // Calculate total deductions
        $this->total_deductions = $this->income_tax + 
                                 $this->social_insurance + 
                                 $this->health_insurance + 
                                 $this->unemployment_insurance + 
                                 $this->union_dues + 
                                 $this->loan_deduction + 
                                 $this->advance_deduction + 
                                 $this->other_deductions;

        // Calculate net pay
        $this->net_pay = $this->gross_pay - $this->total_deductions;
    }

    public function calculateTaxes(): void
    {
        // Simplified tax calculation - this should be based on actual tax brackets
        $taxableIncome = $this->gross_pay - $this->social_insurance - $this->health_insurance;
        
        // Basic tax calculation (this should be more sophisticated)
        if ($taxableIncome > 5000000) { // 5M VND threshold
            $this->income_tax = $taxableIncome * 0.1; // 10% tax rate
        } else {
            $this->income_tax = 0;
        }

        // Social insurance (8% of basic salary, max 20 times minimum wage)
        $this->social_insurance = min($this->basic_salary * 0.08, 20 * 1490000 * 0.08);

        // Health insurance (1.5% of basic salary)
        $this->health_insurance = $this->basic_salary * 0.015;

        // Unemployment insurance (1% of basic salary, max 20 times minimum wage)
        $this->unemployment_insurance = min($this->basic_salary * 0.01, 20 * 1490000 * 0.01);
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
        return $this->payment_status === 'paid' && $this->paid_at;
    }

    public function getFormattedPeriodAttribute(): string
    {
        return $this->period_start_date->format('M Y') . ' - ' . $this->period_end_date->format('M Y');
    }

    public function getTotalWorkingHoursAttribute(): float
    {
        return $this->regular_hours + $this->overtime_hours;
    }

    public function getEffectiveHourlyRateAttribute(): float
    {
        $totalHours = $this->total_working_hours;
        return $totalHours > 0 ? $this->gross_pay / $totalHours : 0;
    }
}
