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
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->string('item_type', 20)->default('product')->comment('product, service, material');
            $table->unsignedBigInteger('item_id')->comment('ID of product/service/material');
            $table->string('item_code', 100)->comment('Product/service/material code');
            $table->string('item_name', 300)->comment('Product/service/material name');
            $table->text('item_description')->nullable();
            
            // Quantity and units
            $table->decimal('quantity', 15, 3)->comment('Ordered quantity');
            $table->decimal('delivered_quantity', 15, 3)->default(0)->comment('Delivered quantity');
            $table->decimal('returned_quantity', 15, 3)->default(0)->comment('Returned quantity');
            $table->string('unit', 50)->comment('Unit of measurement');
            
            // Pricing
            $table->decimal('unit_price', 15, 2)->comment('Unit price');
            $table->decimal('discount_percent', 5, 2)->default(0)->comment('Discount percentage');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Discount amount');
            $table->decimal('tax_percent', 5, 2)->default(0)->comment('Tax percentage');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('Tax amount');
            $table->decimal('line_total', 15, 2)->comment('Line total amount');
            
            // Cost tracking
            $table->decimal('unit_cost', 15, 2)->default(0)->comment('Unit cost for profit calculation');
            $table->decimal('total_cost', 15, 2)->default(0)->comment('Total cost for this line');
            
            // Inventory tracking
            $table->string('batch_number', 100)->nullable()->comment('Batch/lot number');
            $table->string('serial_numbers', 1000)->nullable()->comment('Serial numbers (JSON array)');
            $table->date('expiry_date')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'])->default('pending');
            $table->text('notes')->nullable();
            
            $table->timestamps();

            // Indexes
            $table->index(['sales_order_id']);
            $table->index(['item_type', 'item_id']);
            $table->index(['item_code']);
            $table->index(['status']);
            $table->index(['batch_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
    }
};
