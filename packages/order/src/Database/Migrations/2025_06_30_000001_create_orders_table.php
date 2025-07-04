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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('order_number')->unique();
            $table->timestamp('order_date');
            $table->enum('status', ['draft', 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'])->default('draft');
            $table->enum('channel', ['in_store', 'online', 'phone', 'facebook', 'mobile_app'])->default('in_store');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('shipping_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'refunded'])->default('pending');
            $table->text('notes')->nullable();
            $table->json('shipping_address')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes for performance and data cutoff
            $table->index(['store_id', 'order_date', 'status'], 'idx_orders_cutoff');
            $table->index(['store_id', 'customer_id', 'order_date']);
            $table->index(['store_id', 'order_number']);
            $table->index(['status', 'order_date']);
            $table->index(['channel', 'order_date']);

            // Index for archiving
            $table->index(['order_date', 'store_id'], 'idx_orders_archive');

            // Unique constraint
            $table->unique(['store_id', 'order_number'], 'uk_orders_store_number');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->storedAs('(quantity * unit_price) - discount_amount');
            $table->decimal('cost_price', 15, 2)->default(0); // For profit calculation
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['order_id', 'product_id', 'variant_id']);
            $table->index(['product_id', 'created_at']); // For product performance
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};