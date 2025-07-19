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
        Schema::create('material_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('material_categories')->onDelete('cascade');
            $table->string('code', 50)->comment('Category code');
            $table->string('name', 200)->comment('Category name');
            $table->string('slug', 250)->comment('URL-friendly slug');
            $table->text('description')->nullable();
            $table->string('image')->nullable()->comment('Category image path');
            $table->string('icon')->nullable()->comment('Category icon class');
            $table->string('color', 7)->nullable()->comment('Category color hex code');
            $table->integer('sort_order')->default(0)->comment('Display order');
            $table->boolean('is_active')->default(true);
            $table->boolean('show_in_menu')->default(true)->comment('Show in navigation menu');
            $table->json('metadata')->nullable()->comment('Additional category metadata');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'code'], 'unique_store_category_code');
            $table->unique(['store_id', 'slug'], 'unique_store_category_slug');
            $table->index(['store_id', 'parent_id']);
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'show_in_menu']);
            $table->index(['store_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_categories');
    }
};
