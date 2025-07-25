<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\Employees\Models\Employee;
use Packages\Employees\Models\EmployeePayroll;
use Packages\Employees\Models\EmployeeTimesheet;
use Packages\User\Models\User;
use Carbon\Carbon;

class EmployeePayrollSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('💰 Seeding Employee Payroll...');

        $stores = Store::all();

        foreach ($stores as $store) {
            $this->createPayrollForStore($store);
        }

        $this->command->info('✅ Employee Payroll seeded successfully!');
    }

    private function createPayrollForStore(Store $store): void
    {
        $employees = Employee::where('store_id', $store->id)
            ->where('status', 'active')
            ->get();

        if ($employees->isEmpty()) {
            return;
        }

        $processedBy = User::where('email', 'admin@' . $store->domain)->first();

        // Create payroll for the last 3 months
        for ($i = 2; $i >= 0; $i--) {
            $payrollMonth = Carbon::now()->subMonths($i);
            $this->createMonthlyPayroll($store, $employees, $payrollMonth, $processedBy);
        }
    }

    private function createMonthlyPayroll(Store $store, $employees, Carbon $payrollMonth, ?User $processedBy): void
    {
        foreach ($employees as $employee) {
            $this->createEmployeePayroll($employee, $payrollMonth, $processedBy);
        }
    }

    private function createEmployeePayroll(Employee $employee, Carbon $payrollMonth, ?User $processedBy): void
    {
        // Get timesheet data for the month
        $timesheetData = $this->getTimesheetData($employee, $payrollMonth);
        
        // Calculate salary components
        $salaryComponents = $this->calculateSalaryComponents($employee, $timesheetData);
        
        // Calculate deductions
        $deductions = $this->calculateDeductions($employee, $salaryComponents['gross_salary']);
        
        // Calculate net salary
        $netSalary = $salaryComponents['gross_salary'] - $deductions['total_deductions'];
        
        // Generate payroll number
        $payrollNumber = $this->generatePayrollNumber($employee, $payrollMonth);
        
        EmployeePayroll::create([
            'store_id' => $employee->store_id,
            'employee_id' => $employee->id,
            'payroll_period' => 'monthly',
            'period_start_date' => $payrollMonth->copy()->startOfMonth(),
            'period_end_date' => $payrollMonth->copy()->endOfMonth(),
            'pay_date' => $payrollMonth->copy()->endOfMonth()->addDays(5), // Pay 5 days after month end
            'basic_salary' => $employee->basic_salary,
            'hourly_rate' => $this->calculateHourlyRate($employee->basic_salary),
            'regular_hours' => $timesheetData['regular_hours'],
            'overtime_hours' => $timesheetData['overtime_hours'],
            'overtime_rate' => $this->calculateHourlyRate($employee->basic_salary) * 1.5,
            'overtime_pay' => $salaryComponents['overtime_pay'],
            'commission' => $salaryComponents['commission'],
            'bonus' => $salaryComponents['bonus'],
            'allowances' => $salaryComponents['allowances'],
            'holiday_pay' => 0,
            'other_earnings' => 0,
            'gross_pay' => $salaryComponents['gross_salary'],
            'income_tax' => $deductions['income_tax'],
            'social_insurance' => $deductions['social_insurance'],
            'health_insurance' => $deductions['health_insurance'],
            'unemployment_insurance' => $deductions['unemployment_insurance'],
            'union_dues' => $deductions['union_fee'],
            'loan_deduction' => 0,
            'advance_deduction' => 0,
            'other_deductions' => $deductions['other_deductions'],
            'total_deductions' => $deductions['total_deductions'],
            'net_pay' => $netSalary,
            'payment_status' => $this->getPayrollStatus($payrollMonth),
            'payment_method' => $this->getRandomPaymentMethod(),
            'bank_account' => $this->generateBankAccount($employee),
            'payment_reference' => $this->generatePaymentReference($employee),
            'paid_at' => $this->getPayrollStatus($payrollMonth) === 'paid' ?
                $payrollMonth->copy()->endOfMonth()->addDays(5) : null,
            'notes' => $this->generatePayrollNotes($timesheetData, $salaryComponents),
            'calculated_by' => $processedBy?->id,
            'is_approved' => true,
            'approved_by' => $processedBy?->id,
            'approved_at' => $payrollMonth->copy()->endOfMonth()->addDays(4),
            'metadata' => json_encode([
                'created_via' => 'seeder',
                'calculation_method' => 'standard',
                'tax_year' => $payrollMonth->year,
                'payroll_cycle' => 'monthly',
            ]),
        ]);
    }

    private function getTimesheetData(Employee $employee, Carbon $payrollMonth): array
    {
        $startDate = $payrollMonth->copy()->startOfMonth();
        $endDate = $payrollMonth->copy()->endOfMonth();
        
        $timesheets = EmployeeTimesheet::where('employee_id', $employee->id)
            ->whereBetween('work_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();
        
        $regularHours = $timesheets->where('status', '!=', 'absent')->sum('regular_hours');
        $overtimeHours = $timesheets->where('status', '!=', 'absent')->sum('overtime_hours');
        $workedDays = $timesheets->where('status', '!=', 'absent')->count();
        $absentDays = $timesheets->where('status', 'absent')->count();
        $lateDays = $timesheets->where('status', 'late')->count();
        $earlyLeaveDays = $timesheets->where('status', 'early_leave')->count();
        
        return [
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'worked_days' => $workedDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'early_leave_days' => $earlyLeaveDays,
        ];
    }

    private function calculateSalaryComponents(Employee $employee, array $timesheetData): array
    {
        $hourlyRate = $this->calculateHourlyRate($employee->basic_salary);
        
        // Regular pay based on worked hours
        $regularPay = $timesheetData['regular_hours'] * $hourlyRate;
        
        // Overtime pay (1.5x rate)
        $overtimePay = $timesheetData['overtime_hours'] * $hourlyRate * 1.5;
        
        // Commission (random for sales staff)
        $commission = $this->calculateCommission($employee);
        
        // Bonus (random monthly bonus)
        $bonus = $this->calculateBonus($employee);
        
        // Allowances
        $allowances = $this->calculateAllowances($employee);
        
        $grossSalary = $regularPay + $overtimePay + $commission + $bonus + $allowances;
        
        return [
            'regular_pay' => $regularPay,
            'overtime_pay' => $overtimePay,
            'commission' => $commission,
            'bonus' => $bonus,
            'allowances' => $allowances,
            'gross_salary' => $grossSalary,
        ];
    }

    private function calculateHourlyRate(float $basicSalary): float
    {
        // Assuming 22 working days per month, 8 hours per day
        return $basicSalary / (22 * 8);
    }

    private function calculateCommission(Employee $employee): float
    {
        // Random commission for sales positions
        $position = $employee->position->name ?? '';
        
        if (str_contains(strtolower($position), 'sales') || str_contains(strtolower($position), 'bán hàng')) {
            return rand(500000, 2000000); // 500k - 2M VND
        }
        
        return 0;
    }

    private function calculateBonus(Employee $employee): float
    {
        // Random monthly bonus (20% chance)
        if (rand(1, 100) <= 20) {
            return rand(200000, 1000000); // 200k - 1M VND
        }
        
        return 0;
    }

    private function calculateAllowances(Employee $employee): float
    {
        $allowances = 0;
        
        // Transportation allowance
        $allowances += 300000; // 300k VND
        
        // Meal allowance
        $allowances += 500000; // 500k VND
        
        // Phone allowance for managers
        $position = $employee->position->name ?? '';
        if (str_contains(strtolower($position), 'manager') || str_contains(strtolower($position), 'quản lý')) {
            $allowances += 200000; // 200k VND
        }
        
        return $allowances;
    }

    private function calculateDeductions(Employee $employee, float $grossSalary): array
    {
        // Social insurance (8% of basic salary, max 8M VND)
        $socialInsurance = min($employee->basic_salary * 0.08, 640000);
        
        // Health insurance (1.5% of basic salary, max 1.5M VND)
        $healthInsurance = min($employee->basic_salary * 0.015, 112500);
        
        // Unemployment insurance (1% of basic salary, max 884k VND)
        $unemploymentInsurance = min($employee->basic_salary * 0.01, 88400);
        
        // Union fee (1% of basic salary)
        $unionFee = $employee->basic_salary * 0.01;
        
        // Income tax (progressive rates)
        $incomeTax = $this->calculateIncomeTax($grossSalary, $socialInsurance + $healthInsurance + $unemploymentInsurance);
        
        // Other deductions (random)
        $otherDeductions = rand(0, 1) == 1 ? rand(50000, 200000) : 0;
        
        $totalDeductions = $incomeTax + $socialInsurance + $healthInsurance + $unemploymentInsurance + $unionFee + $otherDeductions;
        
        return [
            'income_tax' => $incomeTax,
            'social_insurance' => $socialInsurance,
            'health_insurance' => $healthInsurance,
            'unemployment_insurance' => $unemploymentInsurance,
            'union_fee' => $unionFee,
            'other_deductions' => $otherDeductions,
            'total_deductions' => $totalDeductions,
        ];
    }

    private function calculateIncomeTax(float $grossSalary, float $socialInsuranceDeductions): float
    {
        // Taxable income = gross salary - social insurance deductions - personal deduction (11M VND)
        $taxableIncome = max(0, $grossSalary - $socialInsuranceDeductions - 11000000);
        
        // Progressive tax rates in Vietnam
        $tax = 0;
        
        if ($taxableIncome <= 5000000) {
            $tax = $taxableIncome * 0.05;
        } elseif ($taxableIncome <= 10000000) {
            $tax = 250000 + ($taxableIncome - 5000000) * 0.10;
        } elseif ($taxableIncome <= 18000000) {
            $tax = 750000 + ($taxableIncome - 10000000) * 0.15;
        } elseif ($taxableIncome <= 32000000) {
            $tax = 1950000 + ($taxableIncome - 18000000) * 0.20;
        } else {
            $tax = 4750000 + ($taxableIncome - 32000000) * 0.25;
        }
        
        return $tax;
    }

    private function generatePayrollNumber(Employee $employee, Carbon $payrollMonth): string
    {
        return 'PAY-' . $employee->employee_code . '-' . $payrollMonth->format('Ym');
    }

    private function getPayrollStatus(Carbon $payrollMonth): string
    {
        if ($payrollMonth->lt(Carbon::now()->subMonth())) {
            return 'paid';
        } elseif ($payrollMonth->lt(Carbon::now())) {
            return 'approved';
        } else {
            return 'pending';
        }
    }

    private function getRandomPaymentMethod(): string
    {
        $methods = ['bank_transfer', 'cash', 'check'];
        $weights = [80, 15, 5]; // 80% bank transfer, 15% cash, 5% check
        
        $random = rand(1, 100);
        if ($random <= 80) return 'bank_transfer';
        if ($random <= 95) return 'cash';
        return 'check';
    }

    private function generateBankAccount(Employee $employee): ?string
    {
        // Generate fake bank account number
        $banks = ['VCB', 'TCB', 'ACB', 'VTB', 'CTG'];
        $bank = $banks[array_rand($banks)];
        $accountNumber = str_pad(rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);
        
        return $bank . '-' . $accountNumber;
    }

    private function generatePaymentReference(Employee $employee): string
    {
        return 'REF-PAY-' . $employee->employee_code . '-' . date('Ymd') . '-' . rand(1000, 9999);
    }

    private function generatePayrollNotes(array $timesheetData, array $salaryComponents): ?string
    {
        $notes = [];
        
        if ($timesheetData['overtime_hours'] > 0) {
            $notes[] = "Tăng ca {$timesheetData['overtime_hours']} giờ";
        }
        
        if ($timesheetData['absent_days'] > 0) {
            $notes[] = "Nghỉ {$timesheetData['absent_days']} ngày";
        }
        
        if ($timesheetData['late_days'] > 0) {
            $notes[] = "Đi muộn {$timesheetData['late_days']} ngày";
        }
        
        if ($salaryComponents['commission'] > 0) {
            $notes[] = "Có hoa hồng";
        }
        
        if ($salaryComponents['bonus'] > 0) {
            $notes[] = "Có thưởng tháng";
        }
        
        return empty($notes) ? null : implode(', ', $notes);
    }
}
