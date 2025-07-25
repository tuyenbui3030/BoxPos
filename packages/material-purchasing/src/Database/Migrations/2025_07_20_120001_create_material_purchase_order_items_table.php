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
        Schema::create('material_purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('material_purchase_orders')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('building_materials')->onDelete('restrict');
            $table->string('material_code', 100)->comment('Material code at time of order');
            $table->string('material_name', 300)->comment('Material name at time of order');
            $table->text('material_description')->nullable();
            
            // Quantity and units
            $table->decimal('quantity_ordered', 15, 3)->comment('Ordered quantity');
            $table->decimal('quantity_received', 15, 3)->default(0)->comment('Received quantity');
            $table->decimal('quantity_pending', 15, 3)->default(0)->comment('Pending quantity');
            $table->decimal('quantity_cancelled', 15, 3)->default(0)->comment('Cancelled quantity');
            $table->string('unit', 50)->comment('Unit of measurement');
            
            // Pricing
            $table->decimal('unit_price', 15, 2)->comment('Unit price');
            $table->decimal('discount_percent', 5, 2)->default(0)->comment('Discount percentage');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Discount amount');
            $table->decimal('tax_percent', 5, 2)->default(0)->comment('Tax percentage');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('Tax amount');
            $table->decimal('line_total', 15, 2)->comment('Line total amount');
            
            // Delivery tracking
            $table->date('expected_delivery_date')->nullable()->comment('Expected delivery date for this item');
            $table->date('actual_delivery_date')->nullable()->comment('Actual delivery date for this item');
            $table->enum('delivery_status', ['pending', 'partial', 'delivered', 'cancelled'])->default('pending');
            
            // Quality and specifications
            $table->json('specifications')->nullable()->comment('Item specifications');
            $table->text('quality_requirements')->nullable()->comment('Quality requirements');
            $table->boolean('quality_checked')->default(false)->comment('Quality check completed');
            $table->enum('quality_status', ['pending', 'passed', 'failed', 'conditional'])->nullable();
            $table->text('quality_notes')->nullable();
            
            // Inventory tracking
            $table->string('batch_number', 100)->nullable()->comment('Batch/lot number');
            $table->string('serial_numbers', 1000)->nullable()->comment('Serial numbers (JSON array)');
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'confirmed', 'partial_received', 'received', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional item metadata');
            
            $table->timestamps();

            // Indexes
            $table->index(['purchase_order_id']);
            $table->index(['material_id']);
            $table->index(['material_code']);
            $table->index(['status']);
            $table->index(['delivery_status']);
            $table->index(['quality_status']);
            $table->index(['expected_delivery_date']);
            $table->index(['batch_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_purchase_order_items');
    }
};
