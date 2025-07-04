<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tạo bảng hình ảnh sản phẩm
     */
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->string('image_path'); // Đường dẫn file ảnh
            $table->string('alt_text')->nullable(); // Alt text cho SEO
            $table->integer('sort_order')->default(0); // Thứ tự hiển thị
            $table->boolean('is_primary')->default(false); // Ảnh chính
            $table->enum('type', ['main', 'gallery', 'thumbnail'])->default('gallery');
            $table->json('metadata')->nullable(); // Metadata (size, format, etc.)
            $table->timestamps();

            // Indexes for performance
            $table->index(['product_id', 'sort_order'], 'idx_images_product_sort');
            $table->index(['product_id', 'is_primary'], 'idx_images_product_primary');
            $table->index(['variant_id', 'sort_order'], 'idx_images_variant_sort');
            $table->index(['type'], 'idx_images_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};