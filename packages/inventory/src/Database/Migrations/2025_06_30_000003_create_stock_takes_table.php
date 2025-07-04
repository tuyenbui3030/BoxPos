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
        Schema::create('stock_takes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->string('reference_number')->unique();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->date('scheduled_date');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'status', 'scheduled_date']);
            $table->index(['store_id', 'reference_number']);
            $table->index(['status', 'scheduled_date']);
        });

        Schema::create('stock_take_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_take_id')->constrained('stock_takes')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->decimal('system_quantity', 15, 3); // Quantity from system
            $table->decimal('counted_quantity', 15, 3)->nullable(); // Actual counted quantity
            $table->decimal('variance', 15, 3)->storedAs('counted_quantity - system_quantity');
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['stock_take_id', 'product_id', 'variant_id']);
            $table->index(['stock_take_id', 'variance']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_take_items');
        Schema::dropIfExists('stock_takes');
    }
};