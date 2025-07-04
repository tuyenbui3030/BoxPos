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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'name']);

            // Unique constraint
            $table->unique(['store_id', 'name'], 'uk_departments_store_name');
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0); // Percentage
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'department_id', 'is_active']);
            $table->index(['store_id', 'name']);

            // Unique constraint
            $table->unique(['store_id', 'department_id', 'name'], 'uk_positions_store_dept_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
    }
};