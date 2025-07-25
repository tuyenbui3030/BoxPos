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
        Schema::create('stock_takes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('stock_take_number', 50)->comment('Stock take reference number');
            $table->string('name', 200)->comment('Stock take name/title');
            $table->text('description')->nullable();
            
            // Stock take details
            $table->date('scheduled_date')->comment('Scheduled stock take date');
            $table->date('start_date')->nullable()->comment('Actual start date');
            $table->date('end_date')->nullable()->comment('Actual end date');
            $table->enum('type', ['full', 'partial', 'cycle', 'spot'])->default('full')->comment('Stock take type');
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled', 'approved'])->default('planned');
            
            // Scope and filters
            $table->json('material_categories')->nullable()->comment('Categories to include');
            $table->json('material_filters')->nullable()->comment('Additional material filters');
            $table->string('location_filter', 200)->nullable()->comment('Location/zone filter');
            $table->boolean('include_zero_stock')->default(true)->comment('Include items with zero stock');
            $table->boolean('include_negative_stock')->default(true)->comment('Include items with negative stock');
            
            // Count details
            $table->integer('total_materials')->default(0)->comment('Total materials to count');
            $table->integer('counted_materials')->default(0)->comment('Materials already counted');
            $table->integer('variance_count')->default(0)->comment('Number of items with variance');
            $table->decimal('total_variance_value', 15, 2)->default(0)->comment('Total value of variances');
            
            // Team and assignments
            $table->json('assigned_users')->nullable()->comment('Users assigned to this stock take');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Approval workflow
            $table->boolean('requires_approval')->default(true);
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Adjustments
            $table->boolean('auto_adjust')->default(false)->comment('Automatically adjust inventory');
            $table->boolean('adjustments_posted')->default(false)->comment('Adjustments have been posted');
            $table->timestamp('adjustments_posted_at')->nullable();
            $table->foreignId('adjustments_posted_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Additional information
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional stock take data');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'stock_take_number'], 'unique_store_stock_take_number');
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'scheduled_date']);
            $table->index(['store_id', 'type']);
            $table->index(['status', 'scheduled_date']);
            $table->index(['supervisor_id']);
            $table->index(['requires_approval', 'is_approved']);
            $table->index(['created_by']);
            $table->index(['approved_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_takes');
    }
};
