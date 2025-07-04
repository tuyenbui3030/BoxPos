<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tạo bảng biến thể sản phẩm (size, color, etc.)
     */
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('name'); // e.g., "Red - Large", "Size M"
            $table->string('sku')->nullable(); // SKU riêng cho biến thể
            $table->string('barcode')->nullable();
            $table->decimal('cost_price', 15, 2)->nullable(); // Override parent product price
            $table->decimal('sale_price', 15, 2)->nullable(); // Override parent product price
            $table->decimal('wholesale_price', 15, 2)->nullable(); // Giá bán buôn
            $table->json('attributes'); // {"color": "red", "size": "large"}
            $table->string('image_path')->nullable(); // Hình ảnh riêng
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for performance
            $table->index(['product_id', 'is_active'], 'idx_variants_product_active');
            $table->index(['product_id', 'sort_order'], 'idx_variants_product_sort');
            $table->index(['sku'], 'idx_variants_sku');
            $table->index(['barcode'], 'idx_variants_barcode');

            // Unique constraints
            $table->unique(['product_id', 'sku'], 'uk_variants_product_sku');
            $table->unique(['product_id', 'barcode'], 'uk_variants_product_barcode');
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