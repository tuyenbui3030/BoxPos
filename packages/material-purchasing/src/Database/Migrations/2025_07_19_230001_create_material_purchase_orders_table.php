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
        Schema::create('material_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('material_suppliers')->onDelete('restrict');
            $table->string('po_number', 50)->comment('Purchase order number');
            $table->string('supplier_reference', 100)->nullable()->comment('Supplier reference number');
            
            // Order details
            $table->date('order_date')->comment('Order date');
            $table->date('expected_delivery_date')->nullable()->comment('Expected delivery date');
            $table->date('actual_delivery_date')->nullable()->comment('Actual delivery date');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['draft', 'pending', 'approved', 'sent', 'confirmed', 'partial_received', 'received', 'cancelled', 'closed'])->default('draft');
            
            // Financial details
            $table->decimal('subtotal', 15, 2)->default(0)->comment('Subtotal amount');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('Tax amount');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Discount amount');
            $table->decimal('shipping_cost', 15, 2)->default(0)->comment('Shipping cost');
            $table->decimal('other_charges', 15, 2)->default(0)->comment('Other charges');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('Total amount');
            $table->string('currency', 3)->default('VND');
            
            // Payment terms
            $table->enum('payment_terms', ['cash', 'cod', 'net_15', 'net_30', 'net_60', 'net_90'])->default('net_30');
            $table->date('payment_due_date')->nullable();
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            
            // Delivery details
            $table->text('delivery_address')->nullable();
            $table->string('delivery_contact', 100)->nullable();
            $table->string('delivery_phone', 20)->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->enum('delivery_method', ['pickup', 'delivery', 'shipping'])->default('delivery');
            $table->string('tracking_number', 100)->nullable();
            
            // Approval workflow
            $table->foreignId('requested_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->json('attachments')->nullable()->comment('Attached documents');
            $table->boolean('is_recurring')->default(false)->comment('Recurring order flag');
            $table->string('recurring_frequency', 20)->nullable()->comment('Recurring frequency');
            
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'po_number'], 'unique_store_po_number');
            $table->index(['store_id', 'supplier_id']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'order_date']);
            $table->index(['store_id', 'expected_delivery_date']);
            $table->index(['supplier_id', 'status']);
            $table->index(['payment_status']);
            $table->index(['requested_by']);
            $table->index(['approved_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_purchase_orders');
    }
};
