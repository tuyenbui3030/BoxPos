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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('set null');
            $table->string('sku', 100)->comment('Stock Keeping Unit');
            $table->string('barcode', 100)->nullable()->comment('Product barcode');
            $table->string('name', 300)->comment('Product name');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            
            // Product type and classification
            $table->enum('type', ['simple', 'variable', 'grouped', 'bundle'])->default('simple');
            $table->enum('status', ['active', 'inactive', 'draft', 'discontinued'])->default('active');
            $table->boolean('is_digital')->default(false)->comment('Digital/downloadable product');
            $table->boolean('is_service')->default(false)->comment('Service product');
            
            // Pricing
            $table->decimal('cost_price', 15, 2)->default(0)->comment('Cost price');
            $table->decimal('selling_price', 15, 2)->default(0)->comment('Regular selling price');
            $table->decimal('sale_price', 15, 2)->nullable()->comment('Sale price');
            $table->date('sale_price_start')->nullable();
            $table->date('sale_price_end')->nullable();
            $table->string('currency', 3)->default('VND');
            
            // Tax settings
            $table->boolean('is_taxable')->default(true);
            $table->decimal('tax_rate', 5, 2)->default(0)->comment('Tax rate percentage');
            $table->string('tax_class', 50)->nullable()->comment('Tax class');
            
            // Inventory tracking
            $table->boolean('track_inventory')->default(true);
            $table->decimal('stock_quantity', 15, 3)->default(0)->comment('Current stock quantity');
            $table->decimal('reserved_quantity', 15, 3)->default(0)->comment('Reserved/allocated quantity');
            $table->decimal('available_quantity', 15, 3)->default(0)->comment('Available quantity');
            $table->string('unit', 50)->default('pcs')->comment('Unit of measurement');
            $table->decimal('min_stock_level', 15, 3)->default(0)->comment('Minimum stock level');
            $table->decimal('max_stock_level', 15, 3)->default(0)->comment('Maximum stock level');
            $table->decimal('reorder_point', 15, 3)->default(0)->comment('Reorder point');
            $table->decimal('reorder_quantity', 15, 3)->default(0)->comment('Reorder quantity');
            
            // Physical attributes
            $table->decimal('weight', 10, 3)->nullable()->comment('Product weight');
            $table->string('weight_unit', 10)->default('kg');
            $table->decimal('length', 10, 2)->nullable()->comment('Product length');
            $table->decimal('width', 10, 2)->nullable()->comment('Product width');
            $table->decimal('height', 10, 2)->nullable()->comment('Product height');
            $table->string('dimension_unit', 10)->default('cm');
            
            // Media
            $table->json('images')->nullable()->comment('Product images');
            $table->string('featured_image', 500)->nullable()->comment('Featured image URL');
            
            // SEO and marketing
            $table->string('meta_title', 200)->nullable();
            $table->text('meta_description')->nullable();
            $table->json('tags')->nullable()->comment('Product tags');
            
            // Supplier information
            $table->string('supplier_sku', 100)->nullable()->comment('Supplier SKU');
            $table->string('manufacturer', 200)->nullable();
            $table->string('brand', 200)->nullable();
            $table->string('model', 100)->nullable();
            
            // Additional settings
            $table->boolean('allow_backorder')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('requires_shipping')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('attributes')->nullable()->comment('Custom product attributes');
            $table->json('metadata')->nullable()->comment('Additional product data');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'sku'], 'unique_store_product_sku');
            $table->index(['store_id', 'category_id']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'type']);
            $table->index(['barcode']);
            $table->index(['name']);
            $table->index(['is_featured']);
            $table->index(['track_inventory']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
