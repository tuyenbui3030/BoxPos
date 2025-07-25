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
        Schema::create('employee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('schedule_date')->comment('Scheduled date');
            
            // Shift information
            $table->string('shift_name', 100)->nullable()->comment('Shift name (Morning, Evening, Night)');
            $table->time('start_time')->comment('Shift start time');
            $table->time('end_time')->comment('Shift end time');
            $table->time('break_start_time')->nullable()->comment('Break start time');
            $table->time('break_end_time')->nullable()->comment('Break end time');
            $table->decimal('scheduled_hours', 5, 2)->default(0)->comment('Scheduled working hours');
            
            // Schedule status
            $table->enum('status', ['scheduled', 'confirmed', 'cancelled', 'completed', 'no_show'])->default('scheduled');
            $table->enum('schedule_type', ['regular', 'overtime', 'holiday', 'special'])->default('regular');
            
            // Location and department
            $table->string('work_location', 200)->nullable()->comment('Work location');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->string('role_for_shift', 100)->nullable()->comment('Role for this shift');
            
            // Availability and conflicts
            $table->boolean('is_available')->default(true)->comment('Employee availability');
            $table->text('unavailable_reason')->nullable()->comment('Reason for unavailability');
            $table->boolean('has_conflict')->default(false)->comment('Schedule conflict flag');
            $table->text('conflict_details')->nullable()->comment('Conflict details');
            
            // Approval workflow
            $table->boolean('is_approved')->default(false)->comment('Schedule approved');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Shift swapping
            $table->foreignId('original_employee_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->boolean('is_swap_request')->default(false)->comment('Is this a swap request');
            $table->enum('swap_status', ['pending', 'approved', 'rejected', 'completed'])->nullable();
            $table->foreignId('swap_approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('swap_approved_at')->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional schedule data');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'employee_id', 'schedule_date']);
            $table->index(['store_id', 'schedule_date']);
            $table->index(['employee_id', 'schedule_date']);
            $table->index(['employee_id', 'status']);
            $table->index(['schedule_date', 'status']);
            $table->index(['department_id']);
            $table->index(['is_approved']);
            $table->index(['is_swap_request']);
            $table->index(['swap_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_schedules');
    }
};
