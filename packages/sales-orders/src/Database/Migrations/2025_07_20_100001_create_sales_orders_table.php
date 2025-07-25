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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('order_number', 50)->comment('Sales order number');
            $table->string('customer_reference', 100)->nullable()->comment('Customer reference number');
            
            // Order details
            $table->date('order_date')->comment('Order date');
            $table->date('delivery_date')->nullable()->comment('Requested delivery date');
            $table->date('actual_delivery_date')->nullable()->comment('Actual delivery date');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['draft', 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'])->default('draft');
            $table->enum('order_type', ['sale', 'quote', 'return', 'exchange'])->default('sale');
            
            // Financial details
            $table->decimal('subtotal', 15, 2)->default(0)->comment('Subtotal amount');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('Tax amount');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Discount amount');
            $table->decimal('shipping_cost', 15, 2)->default(0)->comment('Shipping cost');
            $table->decimal('other_charges', 15, 2)->default(0)->comment('Other charges');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('Total amount');
            $table->string('currency', 3)->default('VND');
            
            // Payment details
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'refunded', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'e_wallet', 'cod', 'credit'])->nullable();
            $table->decimal('paid_amount', 15, 2)->default(0)->comment('Amount already paid');
            $table->decimal('balance_due', 15, 2)->default(0)->comment('Remaining balance');
            $table->date('payment_due_date')->nullable();
            
            // Delivery details
            $table->text('delivery_address')->nullable();
            $table->string('delivery_contact', 100)->nullable();
            $table->string('delivery_phone', 20)->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->enum('delivery_method', ['pickup', 'delivery', 'shipping'])->default('pickup');
            $table->string('tracking_number', 100)->nullable();
            
            // Sales channel
            $table->enum('sales_channel', ['in_store', 'online', 'phone', 'mobile_app', 'facebook', 'website'])->default('in_store');
            
            // Staff information
            $table->foreignId('sales_person_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional order metadata');
            $table->boolean('is_recurring')->default(false)->comment('Recurring order flag');
            $table->string('recurring_frequency', 20)->nullable()->comment('Recurring frequency');
            
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'order_number'], 'unique_store_order_number');
            $table->index(['store_id', 'customer_id']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'order_date']);
            $table->index(['store_id', 'delivery_date']);
            $table->index(['store_id', 'sales_channel']);
            $table->index(['customer_id', 'status']);
            $table->index(['payment_status']);
            $table->index(['sales_person_id']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
