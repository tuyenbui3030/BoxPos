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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('invoice_number', 50)->comment('Invoice number');
            $table->string('tax_invoice_number', 50)->nullable()->comment('Tax invoice number');
            
            // Invoice details
            $table->date('invoice_date')->comment('Invoice date');
            $table->date('due_date')->nullable()->comment('Payment due date');
            $table->enum('invoice_type', ['sale', 'return', 'credit_note', 'debit_note'])->default('sale');
            $table->enum('status', ['draft', 'sent', 'viewed', 'paid', 'overdue', 'cancelled', 'refunded'])->default('draft');
            
            // Financial details
            $table->decimal('subtotal', 15, 2)->default(0)->comment('Subtotal amount');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('Tax amount');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Discount amount');
            $table->decimal('shipping_cost', 15, 2)->default(0)->comment('Shipping cost');
            $table->decimal('other_charges', 15, 2)->default(0)->comment('Other charges');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('Total amount');
            $table->string('currency', 3)->default('VND');
            
            // Payment tracking
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'refunded', 'cancelled'])->default('pending');
            $table->decimal('paid_amount', 15, 2)->default(0)->comment('Amount already paid');
            $table->decimal('balance_due', 15, 2)->default(0)->comment('Remaining balance');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'e_wallet', 'cod', 'credit'])->nullable();
            
            // Customer details (snapshot)
            $table->string('customer_name', 200)->nullable();
            $table->string('customer_email', 100)->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            
            // Tax information
            $table->string('customer_tax_code', 50)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0)->comment('Tax rate percentage');
            $table->boolean('is_tax_inclusive')->default(false);
            
            // E-invoice integration
            $table->boolean('is_e_invoice')->default(false);
            $table->string('e_invoice_code', 100)->nullable();
            $table->timestamp('e_invoice_sent_at')->nullable();
            $table->json('e_invoice_data')->nullable();
            
            // Staff information
            $table->foreignId('sales_person_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->json('metadata')->nullable()->comment('Additional invoice metadata');
            
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'invoice_number'], 'unique_store_invoice_number');
            $table->index(['store_id', 'customer_id']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'invoice_date']);
            $table->index(['store_id', 'due_date']);
            $table->index(['sales_order_id']);
            $table->index(['customer_id', 'status']);
            $table->index(['payment_status']);
            $table->index(['sales_person_id']);
            $table->index(['tax_invoice_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
