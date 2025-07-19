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
        Schema::create('material_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('building_materials')->onDelete('cascade');
            $table->string('location_code', 50)->comment('Warehouse/storage location code');
            $table->string('location_name', 200)->comment('Location display name');
            $table->string('zone', 100)->nullable()->comment('Storage zone/area');
            $table->string('aisle', 50)->nullable()->comment('Aisle number');
            $table->string('shelf', 50)->nullable()->comment('Shelf number');
            $table->string('bin', 50)->nullable()->comment('Bin location');
            
            // Stock quantities
            $table->decimal('quantity_on_hand', 15, 3)->default(0)->comment('Physical quantity on hand');
            $table->decimal('quantity_reserved', 15, 3)->default(0)->comment('Reserved quantity');
            $table->decimal('quantity_available', 15, 3)->default(0)->comment('Available quantity');
            $table->decimal('quantity_incoming', 15, 3)->default(0)->comment('Incoming quantity (POs)');
            $table->decimal('quantity_outgoing', 15, 3)->default(0)->comment('Outgoing quantity (orders)');
            
            // Batch/Lot tracking
            $table->string('batch_number', 100)->nullable()->comment('Batch/lot number');
            $table->string('serial_numbers', 1000)->nullable()->comment('Serial numbers (JSON array)');
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('received_date')->nullable();
            
            // Cost tracking
            $table->decimal('unit_cost', 15, 2)->default(0)->comment('Unit cost for this batch');
            $table->decimal('total_cost', 15, 2)->default(0)->comment('Total cost for this inventory');
            $table->string('cost_method', 20)->default('fifo')->comment('Cost calculation method');
            
            // Quality & condition
            $table->enum('condition', ['new', 'good', 'fair', 'damaged', 'expired'])->default('new');
            $table->boolean('quality_checked')->default(false);
            $table->date('last_quality_check')->nullable();
            $table->text('quality_notes')->nullable();
            
            // Tracking
            $table->decimal('min_stock_level', 15, 3)->default(0)->comment('Minimum stock level for this location');
            $table->decimal('max_stock_level', 15, 3)->nullable()->comment('Maximum stock level for this location');
            $table->timestamp('last_movement_at')->nullable()->comment('Last stock movement');
            $table->integer('movement_count')->default(0)->comment('Number of movements');
            
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'material_id', 'location_code', 'batch_number'], 'unique_inventory_location_batch');
            $table->index(['store_id', 'material_id']);
            $table->index(['store_id', 'location_code']);
            $table->index(['material_id', 'quantity_available']);
            $table->index(['material_id', 'expiry_date']);
            $table->index(['material_id', 'condition']);
            $table->index(['batch_number']);
            $table->index(['last_movement_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_inventory');
    }
};
