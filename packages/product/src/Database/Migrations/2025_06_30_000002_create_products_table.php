<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tạo bảng sản phẩm theo nghiệp vụ trong spec.md
     * Hỗ trợ cả hàng hóa thông thường và F&B
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('set null');
            
            // Thông tin cơ bản
            $table->string('name'); // Tên sản phẩm
            $table->string('code'); // Mã sản phẩm
            $table->string('barcode')->nullable(); // Mã vạch
            $table->text('description')->nullable(); // Mô tả
            $table->string('unit')->default('pcs'); // Đơn vị tính (pieces, kg, liter, etc.)
            
            // Giá cả
            $table->decimal('cost_price', 15, 2)->default(0); // Giá vốn
            $table->decimal('sale_price', 15, 2)->default(0); // Giá bán
            $table->decimal('wholesale_price', 15, 2)->nullable(); // Giá bán buôn
            
            // Quản lý kho
            $table->integer('min_stock')->default(0); // Tồn kho tối thiểu
            $table->integer('max_stock')->nullable(); // Tồn kho tối đa
            $table->boolean('track_stock')->default(true); // Theo dõi tồn kho
            
            // Thuế và chiết khấu
            $table->decimal('tax_rate', 5, 2)->default(0); // Thuế suất (%)
            $table->decimal('discount_rate', 5, 2)->default(0); // Chiết khấu (%)
            
            // Trạng thái và phân loại
            $table->boolean('is_active')->default(true);
            $table->boolean('is_service')->default(false); // Dịch vụ hay hàng hóa
            $table->boolean('is_combo')->default(false); // Combo/Set meal
            $table->enum('type', ['product', 'service', 'combo'])->default('product');
            
            // Thông tin bổ sung
            $table->json('attributes')->nullable(); // Thuộc tính tùy chỉnh
            $table->json('variants')->nullable(); // Biến thể (size, color, etc.)
            $table->string('image_path')->nullable(); // Hình ảnh chính
            $table->json('gallery')->nullable(); // Thư viện ảnh
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for performance (multi-tenant optimized)
            $table->index(['store_id', 'is_active', 'created_at'], 'idx_products_store_active_created');
            $table->index(['store_id', 'category_id'], 'idx_products_store_category');
            $table->index(['store_id', 'code'], 'idx_products_store_code');
            $table->index(['store_id', 'barcode'], 'idx_products_store_barcode');
            $table->index(['store_id', 'name'], 'idx_products_store_name');
            $table->index(['store_id', 'type'], 'idx_products_store_type');
            $table->index(['cost_price'], 'idx_products_cost_price');
            $table->index(['sale_price'], 'idx_products_sale_price');

            // Unique constraints
            $table->unique(['store_id', 'code'], 'uk_products_store_code');
            $table->unique(['store_id', 'barcode'], 'uk_products_store_barcode');
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
