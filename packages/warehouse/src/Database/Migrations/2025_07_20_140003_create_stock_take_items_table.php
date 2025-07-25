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
        Schema::create('stock_take_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_take_id')->constrained('stock_takes')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('building_materials')->onDelete('restrict');
            $table->string('material_code', 100)->comment('Material code at time of count');
            $table->string('material_name', 300)->comment('Material name at time of count');
            
            // System vs Physical quantities
            $table->decimal('system_quantity', 15, 3)->default(0)->comment('System recorded quantity');
            $table->decimal('physical_quantity', 15, 3)->nullable()->comment('Physical counted quantity');
            $table->decimal('variance_quantity', 15, 3)->default(0)->comment('Variance (physical - system)');
            $table->string('unit', 50)->comment('Unit of measurement');
            
            // Cost and value
            $table->decimal('unit_cost', 15, 2)->default(0)->comment('Unit cost at time of count');
            $table->decimal('system_value', 15, 2)->default(0)->comment('System value');
            $table->decimal('physical_value', 15, 2)->default(0)->comment('Physical value');
            $table->decimal('variance_value', 15, 2)->default(0)->comment('Variance value');
            
            // Count details
            $table->enum('count_status', ['pending', 'counted', 'recounted', 'verified', 'adjusted'])->default('pending');
            $table->integer('count_attempts')->default(0)->comment('Number of count attempts');
            $table->timestamp('first_counted_at')->nullable();
            $table->timestamp('last_counted_at')->nullable();
            $table->foreignId('counted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            
            // Batch/Serial tracking
            $table->string('batch_number', 100)->nullable()->comment('Batch/lot number');
            $table->json('serial_numbers')->nullable()->comment('Serial numbers found');
            $table->date('expiry_date')->nullable();
            
            // Location details
            $table->string('location', 200)->nullable()->comment('Physical location');
            $table->string('warehouse_zone', 100)->nullable()->comment('Warehouse zone/area');
            $table->string('bin_location', 100)->nullable()->comment('Specific bin location');
            
            // Variance analysis
            $table->enum('variance_reason', [
                'none', 'damaged', 'expired', 'theft', 'misplaced', 'system_error', 
                'receiving_error', 'shipping_error', 'other'
            ])->nullable()->comment('Reason for variance');
            $table->text('variance_notes')->nullable()->comment('Notes about variance');
            $table->boolean('requires_investigation')->default(false)->comment('Variance requires investigation');
            $table->boolean('investigation_completed')->default(false);
            $table->text('investigation_notes')->nullable();
            
            // Adjustment tracking
            $table->boolean('adjustment_required')->default(false)->comment('Requires inventory adjustment');
            $table->boolean('adjustment_posted')->default(false)->comment('Adjustment has been posted');
            $table->foreignId('adjustment_posted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('adjustment_posted_at')->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional item data');
            
            $table->timestamps();

            // Indexes
            $table->unique(['stock_take_id', 'material_id', 'batch_number'], 'unique_stock_take_material_batch');
            $table->index(['stock_take_id', 'count_status']);
            $table->index(['stock_take_id', 'variance_quantity']);
            $table->index(['material_id']);
            $table->index(['count_status']);
            $table->index(['variance_reason']);
            $table->index(['requires_investigation']);
            $table->index(['adjustment_required', 'adjustment_posted']);
            $table->index(['counted_by']);
            $table->index(['verified_by']);
            $table->index(['batch_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_take_items');
    }
};
