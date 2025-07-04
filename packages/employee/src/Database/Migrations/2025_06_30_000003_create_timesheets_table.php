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
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->integer('break_minutes')->default(0);
            $table->decimal('total_hours', 5, 2)->nullable();
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'employee_id', 'date']);
            $table->index(['store_id', 'date', 'status']);
            $table->index(['employee_id', 'date']);

            // Unique constraint
            $table->unique(['store_id', 'employee_id', 'date'], 'uk_timesheets_employee_date');
        });

        Schema::create('payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->decimal('overtime_pay', 15, 2)->default(0);
            $table->decimal('commission', 15, 2)->default(0);
            $table->decimal('allowances', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('gross_pay', 15, 2)->storedAs('base_salary + overtime_pay + commission + allowances');
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('net_pay', 15, 2)->storedAs('gross_pay - deductions - tax');
            $table->enum('status', ['draft', 'calculated', 'approved', 'paid'])->default('draft');
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'employee_id', 'period_start', 'period_end']);
            $table->index(['store_id', 'status']);
            $table->index(['period_start', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll');
        Schema::dropIfExists('timesheets');
    }
};