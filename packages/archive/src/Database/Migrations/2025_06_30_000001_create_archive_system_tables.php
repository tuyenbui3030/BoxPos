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
        // Archive Configuration
        Schema::create('archive_configs', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->enum('cutoff_period', ['monthly', 'quarterly', 'yearly'])->default('yearly');
            $table->integer('retention_months')->default(12); // Keep data for X months before archiving
            $table->date('last_cutoff_date')->nullable();
            $table->date('next_cutoff_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['table_name', 'is_active']);
            $table->index(['next_cutoff_date']);

            // Unique constraint
            $table->unique(['table_name'], 'uk_archive_configs_table');
        });

        // Archive Batches
        Schema::create('archive_batches', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->date('cutoff_date');
            $table->integer('records_count')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index(['table_name', 'status']);
            $table->index(['cutoff_date', 'table_name']);
            $table->index(['status', 'created_at']);
        });

        // Data Retention Policies
        Schema::create('data_retention_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('table_name');
            $table->integer('retention_period')->default(12); // months
            $table->integer('archive_after')->default(6); // months
            $table->integer('delete_after')->nullable(); // months, null = never delete
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'table_name']);
            $table->index(['store_id', 'is_active']);

            // Unique constraint
            $table->unique(['store_id', 'table_name'], 'uk_retention_policies_store_table');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_retention_policies');
        Schema::dropIfExists('archive_batches');
        Schema::dropIfExists('archive_configs');
    }
};