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
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('position_id')->constrained('positions')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Link to system user
            $table->string('employee_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('hire_date');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern'])->default('full_time');
            $table->enum('status', ['active', 'inactive', 'terminated', 'on_leave'])->default('active');
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->json('emergency_contact')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'employee_code']);
            $table->index(['store_id', 'department_id']);
            $table->index(['store_id', 'first_name', 'last_name']);

            // Unique constraints
            $table->unique(['store_id', 'employee_code'], 'uk_employees_store_code');
            $table->unique(['store_id', 'email'], 'uk_employees_store_email');
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