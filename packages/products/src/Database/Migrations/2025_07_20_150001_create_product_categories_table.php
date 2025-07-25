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
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('product_categories')->onDelete('cascade');
            $table->string('code', 50)->comment('Category code');
            $table->string('name', 200)->comment('Category name');
            $table->text('description')->nullable();
            $table->string('image', 500)->nullable()->comment('Category image URL');
            $table->string('color', 7)->nullable()->comment('Category color (hex)');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable()->comment('Additional category data');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'code'], 'unique_store_product_category_code');
            $table->index(['store_id', 'parent_id']);
            $table->index(['store_id', 'is_active']);
            $table->index(['sort_order']);
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
