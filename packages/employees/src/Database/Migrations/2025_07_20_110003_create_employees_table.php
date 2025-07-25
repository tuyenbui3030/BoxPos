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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('position_id')->nullable()->constrained('positions')->onDelete('set null');
            $table->foreignId('manager_id')->nullable()->constrained('employees')->onDelete('set null');
            
            // Basic Information
            $table->string('employee_code', 50)->comment('Employee code');
            $table->string('full_name', 200)->comment('Full name');
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('id_number', 50)->nullable()->comment('National ID/CCCD');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name', 200)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            
            // Employment Details
            $table->date('hire_date')->comment('Date of hire');
            $table->date('probation_end_date')->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern', 'freelance'])->default('full_time');
            $table->enum('contract_type', ['permanent', 'fixed_term', 'probation', 'seasonal'])->default('permanent');
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->string('work_location', 200)->nullable();
            
            // Salary Information
            $table->decimal('basic_salary', 15, 2)->default(0)->comment('Basic salary');
            $table->string('salary_grade', 50)->nullable();
            $table->enum('pay_frequency', ['hourly', 'daily', 'weekly', 'monthly'])->default('monthly');
            $table->string('currency', 3)->default('VND');
            $table->date('salary_effective_date')->nullable();
            
            // Work Schedule
            $table->json('working_hours')->nullable()->comment('Working hours configuration');
            $table->integer('weekly_hours')->default(40)->comment('Weekly working hours');
            $table->json('break_time')->nullable()->comment('Break time configuration');
            
            // Benefits
            $table->boolean('health_insurance')->default(false);
            $table->boolean('social_insurance')->default(false);
            $table->integer('vacation_days')->default(0)->comment('Annual vacation days');
            $table->integer('sick_leave_days')->default(0)->comment('Annual sick leave days');
            
            // Tax Information
            $table->string('tax_id', 50)->nullable()->comment('Personal tax ID');
            $table->integer('dependents')->default(0)->comment('Number of dependents');
            
            // Status
            $table->enum('status', ['active', 'inactive', 'terminated', 'on_leave', 'suspended'])->default('active');
            $table->date('termination_date')->nullable();
            $table->text('termination_reason')->nullable();
            
            // Additional Information
            $table->text('notes')->nullable();
            $table->json('skills')->nullable()->comment('Employee skills');
            $table->json('certifications')->nullable()->comment('Professional certifications');
            $table->json('metadata')->nullable()->comment('Additional employee data');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'employee_code'], 'unique_store_employee_code');
            $table->index(['store_id', 'department_id']);
            $table->index(['store_id', 'position_id']);
            $table->index(['store_id', 'status']);
            $table->index(['user_id']);
            $table->index(['manager_id']);
            $table->index(['hire_date']);
            $table->index(['email']);
            $table->index(['phone']);
            $table->index(['id_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
