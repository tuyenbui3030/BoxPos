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
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('return_number', 50)->comment('Return number');
            
            // Return details
            $table->date('return_date')->comment('Return date');
            $table->enum('return_type', ['full_return', 'partial_return', 'exchange', 'warranty'])->default('partial_return');
            $table->enum('status', ['pending', 'approved', 'rejected', 'processed', 'refunded', 'exchanged'])->default('pending');
            $table->enum('return_reason', ['defective', 'wrong_item', 'customer_change_mind', 'damaged_shipping', 'expired', 'other'])->comment('Return reason');
            $table->text('return_reason_detail')->nullable()->comment('Detailed return reason');
            
            // Financial details
            $table->decimal('subtotal', 15, 2)->default(0)->comment('Return subtotal amount');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('Return tax amount');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('Total return amount');
            $table->string('currency', 3)->default('VND');
            
            // Refund details
            $table->enum('refund_method', ['cash', 'card', 'bank_transfer', 'store_credit', 'exchange'])->nullable();
            $table->enum('refund_status', ['pending', 'approved', 'processed', 'completed', 'rejected'])->default('pending');
            $table->decimal('refund_amount', 15, 2)->default(0)->comment('Amount to be refunded');
            $table->decimal('restocking_fee', 15, 2)->default(0)->comment('Restocking fee');
            $table->date('refund_processed_date')->nullable();
            
            // Inventory impact
            $table->boolean('restock_items')->default(true)->comment('Whether to restock returned items');
            $table->enum('item_condition', ['new', 'good', 'fair', 'damaged', 'defective'])->default('good');
            
            // Staff information
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('attachments')->nullable()->comment('Photos or documents');
            $table->json('metadata')->nullable()->comment('Additional return metadata');
            
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'return_number'], 'unique_store_return_number');
            $table->index(['store_id', 'customer_id']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'return_date']);
            $table->index(['sales_order_id']);
            $table->index(['invoice_id']);
            $table->index(['customer_id', 'status']);
            $table->index(['refund_status']);
            $table->index(['processed_by']);
            $table->index(['return_reason']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_returns');
    }
};
