<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tạo bảng lịch sử xuất nhập kho
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->enum('movement_type', ['in', 'out', 'adjustment', 'transfer']);
            $table->string('reference_type')->nullable(); // order, purchase_order, stock_take, disposal, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('quantity_before', 15, 3);
            $table->decimal('quantity_change', 15, 3); // Positive for IN, Negative for OUT
            $table->decimal('quantity_after', 15, 3);
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for performance (optimized for data cutoff)
            $table->index(['store_id', 'created_at', 'movement_type'], 'idx_stock_movements_cutoff');
            $table->index(['store_id', 'product_id', 'variant_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['movement_type', 'created_at']);

            // Index for archiving queries
            $table->index(['created_at', 'store_id'], 'idx_stock_movements_archive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};