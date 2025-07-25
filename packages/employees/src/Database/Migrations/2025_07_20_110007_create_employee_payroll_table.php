<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('payroll_period', 20)->comment('Payroll period (monthly, bi-weekly, weekly)');
            $table->date('period_start_date')->comment('Payroll period start date');
            $table->date('period_end_date')->comment('Payroll period end date');
            $table->date('pay_date')->comment('Actual pay date');
            
            // Basic salary components
            $table->decimal('basic_salary', 15, 2)->default(0)->comment('Basic salary');
            $table->decimal('hourly_rate', 15, 2)->default(0)->comment('Hourly rate');
            $table->decimal('regular_hours', 8, 2)->default(0)->comment('Regular working hours');
            $table->decimal('overtime_hours', 8, 2)->default(0)->comment('Overtime hours');
            $table->decimal('overtime_rate', 15, 2)->default(0)->comment('Overtime hourly rate');
            $table->decimal('overtime_pay', 15, 2)->default(0)->comment('Overtime pay');
            
            // Additional earnings
            $table->decimal('commission', 15, 2)->default(0)->comment('Commission amount');
            $table->decimal('bonus', 15, 2)->default(0)->comment('Bonus amount');
            $table->decimal('allowances', 15, 2)->default(0)->comment('Various allowances');
            $table->decimal('holiday_pay', 15, 2)->default(0)->comment('Holiday pay');
            $table->decimal('other_earnings', 15, 2)->default(0)->comment('Other earnings');
            $table->json('earnings_breakdown')->nullable()->comment('Detailed earnings breakdown');
            
            // Gross pay
            $table->decimal('gross_pay', 15, 2)->default(0)->comment('Total gross pay');
            
            // Deductions
            $table->decimal('income_tax', 15, 2)->default(0)->comment('Income tax');
            $table->decimal('social_insurance', 15, 2)->default(0)->comment('Social insurance');
            $table->decimal('health_insurance', 15, 2)->default(0)->comment('Health insurance');
            $table->decimal('unemployment_insurance', 15, 2)->default(0)->comment('Unemployment insurance');
            $table->decimal('union_dues', 15, 2)->default(0)->comment('Union dues');
            $table->decimal('loan_deduction', 15, 2)->default(0)->comment('Loan deduction');
            $table->decimal('advance_deduction', 15, 2)->default(0)->comment('Advance salary deduction');
            $table->decimal('other_deductions', 15, 2)->default(0)->comment('Other deductions');
            $table->json('deductions_breakdown')->nullable()->comment('Detailed deductions breakdown');
            
            // Net pay
            $table->decimal('total_deductions', 15, 2)->default(0)->comment('Total deductions');
            $table->decimal('net_pay', 15, 2)->default(0)->comment('Net pay amount');
            
            // Payment details
            $table->enum('payment_status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['bank_transfer', 'cash', 'check', 'e_wallet'])->default('bank_transfer');
            $table->string('bank_account', 50)->nullable()->comment('Employee bank account');
            $table->string('payment_reference', 100)->nullable()->comment('Payment reference number');
            $table->timestamp('paid_at')->nullable();
            
            // Approval workflow
            $table->boolean('is_approved')->default(false)->comment('Payroll approved');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional payroll data');
            
            $table->foreignId('calculated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'employee_id', 'period_start_date', 'period_end_date'], 'unique_employee_payroll_period');
            $table->index(['store_id', 'payroll_period']);
            $table->index(['employee_id', 'period_start_date']);
            $table->index(['employee_id', 'payment_status']);
            $table->index(['payment_status']);
            $table->index(['is_approved']);
            $table->index(['approved_by']);
            $table->index(['pay_date']);
            $table->index(['paid_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_payroll');
    }
};
