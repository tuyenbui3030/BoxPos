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
        Schema::create('inventory_disposal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disposal_id')->constrained('inventory_disposals')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('building_materials')->onDelete('restrict');
            $table->string('material_code', 100)->comment('Material code');
            $table->string('material_name', 300)->comment('Material name');
            $table->text('material_description')->nullable();
            
            // Quantity details
            $table->decimal('quantity', 15, 3)->comment('Quantity to dispose');
            $table->string('unit', 50)->comment('Unit of measurement');
            $table->decimal('unit_cost', 15, 2)->default(0)->comment('Unit cost');
            $table->decimal('total_cost', 15, 2)->default(0)->comment('Total cost value');
            
            // Disposal details
            $table->enum('disposal_reason', [
                'damaged', 'expired', 'obsolete', 'defective', 'contaminated', 
                'theft', 'loss', 'quality_issue', 'recall', 'other'
            ])->comment('Specific disposal reason');
            $table->text('disposal_reason_detail')->nullable()->comment('Detailed reason');
            $table->enum('condition', ['damaged', 'expired', 'good', 'fair', 'poor'])->comment('Item condition');
            
            // Batch/Serial tracking
            $table->string('batch_number', 100)->nullable()->comment('Batch/lot number');
            $table->json('serial_numbers')->nullable()->comment('Serial numbers');
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            
            // Location details
            $table->string('location', 200)->nullable()->comment('Storage location');
            $table->string('warehouse_zone', 100)->nullable()->comment('Warehouse zone');
            
            // Recovery details
            $table->decimal('recovery_value', 15, 2)->default(0)->comment('Value recovered from this item');
            $table->text('recovery_notes')->nullable()->comment('Recovery method/notes');
            
            // Processing status
            $table->enum('status', ['pending', 'approved', 'processed', 'completed'])->default('pending');
            $table->boolean('inventory_adjusted')->default(false)->comment('Inventory adjusted for this item');
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Additional information
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional item data');
            
            $table->timestamps();

            // Indexes
            $table->index(['disposal_id']);
            $table->index(['material_id']);
            $table->index(['disposal_reason']);
            $table->index(['status']);
            $table->index(['batch_number']);
            $table->index(['expiry_date']);
            $table->index(['processed_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_disposal_items');
    }
};
