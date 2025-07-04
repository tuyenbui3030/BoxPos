<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tạo bảng danh mục sản phẩm theo nghiệp vụ trong spec.md
     */
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('product_categories')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Cài đặt bổ sung (commission, discount, etc.)
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for performance (multi-tenant optimized)
            $table->index(['store_id', 'is_active', 'sort_order']);
            $table->index(['store_id', 'parent_id']);
            $table->index(['store_id', 'code']);
            $table->index(['store_id', 'name']);

            // Unique constraints
            $table->unique(['store_id', 'code'], 'uk_product_categories_store_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};