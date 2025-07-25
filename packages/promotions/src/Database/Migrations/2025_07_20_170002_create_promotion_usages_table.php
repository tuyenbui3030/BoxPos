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
        Schema::create('promotion_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('promotion_id')->constrained('promotions')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('order_number', 100)->comment('Order/invoice number');
            $table->unsignedBigInteger('order_id')->nullable()->comment('Order ID (sales_order or invoice)');
            $table->string('order_type', 50)->default('sales_order')->comment('Order type');
            
            // Usage details
            $table->datetime('used_at')->comment('When promotion was used');
            $table->decimal('order_subtotal', 15, 2)->default(0)->comment('Order subtotal before discount');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Discount amount applied');
            $table->decimal('order_total', 15, 2)->default(0)->comment('Order total after discount');
            
            // Applied items (for product-specific promotions)
            $table->json('applied_items')->nullable()->comment('Items the promotion was applied to');
            $table->integer('items_count')->default(0)->comment('Number of items affected');
            
            // Customer information
            $table->string('customer_email', 100)->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->string('customer_name', 200)->nullable();
            
            // Channel and context
            $table->string('sales_channel', 50)->nullable()->comment('Sales channel used');
            $table->string('payment_method', 50)->nullable()->comment('Payment method used');
            $table->string('device_type', 50)->nullable()->comment('Device type (mobile, desktop, pos)');
            
            // Validation and verification
            $table->boolean('is_valid')->default(true)->comment('Usage is valid');
            $table->text('validation_notes')->nullable()->comment('Validation notes if invalid');
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('validated_at')->nullable();
            
            // Additional information
            $table->json('metadata')->nullable()->comment('Additional usage data');
            
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'promotion_id']);
            $table->index(['store_id', 'customer_id']);
            $table->index(['store_id', 'used_at']);
            $table->index(['promotion_id', 'customer_id']);
            $table->index(['promotion_id', 'used_at']);
            $table->index(['order_number']);
            $table->index(['order_type', 'order_id']);
            $table->index(['is_valid']);
            $table->index(['sales_channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_usages');
    }
};
