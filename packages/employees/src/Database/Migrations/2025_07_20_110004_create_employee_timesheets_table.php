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
        Schema::create('employee_timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('work_date')->comment('Work date');
            
            // Time tracking
            $table->time('check_in_time')->nullable()->comment('Check in time');
            $table->time('check_out_time')->nullable()->comment('Check out time');
            $table->time('break_start_time')->nullable()->comment('Break start time');
            $table->time('break_end_time')->nullable()->comment('Break end time');
            
            // Calculated hours
            $table->decimal('regular_hours', 5, 2)->default(0)->comment('Regular working hours');
            $table->decimal('overtime_hours', 5, 2)->default(0)->comment('Overtime hours');
            $table->decimal('break_hours', 5, 2)->default(0)->comment('Break hours');
            $table->decimal('total_hours', 5, 2)->default(0)->comment('Total working hours');
            
            // Attendance status
            $table->enum('status', ['present', 'absent', 'late', 'early_leave', 'half_day', 'holiday', 'sick_leave', 'vacation'])->default('present');
            $table->text('absence_reason')->nullable()->comment('Reason for absence');
            $table->boolean('is_approved')->default(false)->comment('Timesheet approved');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Location tracking
            $table->string('check_in_location', 200)->nullable()->comment('Check in location');
            $table->string('check_out_location', 200)->nullable()->comment('Check out location');
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            
            // Device tracking
            $table->string('check_in_device', 100)->nullable()->comment('Check in device info');
            $table->string('check_out_device', 100)->nullable()->comment('Check out device info');
            $table->string('check_in_ip', 45)->nullable()->comment('Check in IP address');
            $table->string('check_out_ip', 45)->nullable()->comment('Check out IP address');
            
            // Additional information
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional timesheet data');
            
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'employee_id', 'work_date'], 'unique_employee_work_date');
            $table->index(['store_id', 'work_date']);
            $table->index(['employee_id', 'work_date']);
            $table->index(['employee_id', 'status']);
            $table->index(['work_date', 'status']);
            $table->index(['is_approved']);
            $table->index(['approved_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_timesheets');
    }
};
