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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('sku', 100)->comment('Variant SKU');
            $table->string('barcode', 100)->nullable()->comment('Variant barcode');
            $table->string('name', 300)->comment('Variant name');
            $table->text('description')->nullable();
            
            // Variant attributes (size, color, etc.)
            $table->json('attributes')->comment('Variant attributes (color, size, etc.)');
            $table->string('attribute_summary', 200)->nullable()->comment('Human readable attribute summary');
            
            // Pricing
            $table->decimal('cost_price', 15, 2)->default(0)->comment('Variant cost price');
            $table->decimal('selling_price', 15, 2)->default(0)->comment('Variant selling price');
            $table->decimal('sale_price', 15, 2)->nullable()->comment('Variant sale price');
            $table->date('sale_price_start')->nullable();
            $table->date('sale_price_end')->nullable();
            
            // Inventory
            $table->decimal('stock_quantity', 15, 3)->default(0)->comment('Variant stock quantity');
            $table->decimal('reserved_quantity', 15, 3)->default(0)->comment('Reserved quantity');
            $table->decimal('available_quantity', 15, 3)->default(0)->comment('Available quantity');
            $table->decimal('min_stock_level', 15, 3)->default(0)->comment('Minimum stock level');
            $table->decimal('reorder_point', 15, 3)->default(0)->comment('Reorder point');
            
            // Physical attributes
            $table->decimal('weight', 10, 3)->nullable()->comment('Variant weight');
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            
            // Media
            $table->json('images')->nullable()->comment('Variant images');
            $table->string('featured_image', 500)->nullable();
            
            // Status and settings
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');
            $table->boolean('is_default')->default(false)->comment('Default variant for product');
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable()->comment('Additional variant data');
            
            $table->timestamps();

            // Indexes
            $table->unique(['sku'], 'unique_variant_sku');
            $table->index(['product_id']);
            $table->index(['product_id', 'status']);
            $table->index(['product_id', 'is_default']);
            $table->index(['barcode']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
