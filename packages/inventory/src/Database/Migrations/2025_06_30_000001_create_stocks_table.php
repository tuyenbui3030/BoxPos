<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tạo bảng tồn kho theo nghiệp vụ quản lý kho
     */
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('stores')->onDelete('cascade'); // For multi-branch
            $table->decimal('quantity', 15, 3)->default(0);
            $table->decimal('reserved_quantity', 15, 3)->default(0); // For pending orders
            $table->decimal('available_quantity', 15, 3)->storedAs('quantity - reserved_quantity');
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->timestamp('last_updated')->useCurrent();
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for performance (multi-tenant optimized)
            $table->index(['store_id', 'product_id', 'variant_id', 'branch_id'], 'idx_stocks_lookup');
            $table->index(['store_id', 'quantity']);
            $table->index(['store_id', 'last_updated']);
            $table->index(['product_id', 'variant_id']);

            // Unique constraint for stock tracking
            $table->unique(['store_id', 'product_id', 'variant_id', 'branch_id'], 'uk_stocks_unique');

            // Check constraints will be added later via raw SQL if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};