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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('restrict');
            $table->string('payment_number', 100)->comment('Payment reference number');
            
            // Related order/invoice
            $table->string('order_number', 100)->comment('Related order/invoice number');
            $table->unsignedBigInteger('order_id')->comment('Related order/invoice ID');
            $table->string('order_type', 50)->default('sales_order')->comment('Order type (sales_order, invoice)');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            
            // Payment details
            $table->datetime('payment_date')->comment('Payment date and time');
            $table->decimal('amount', 15, 2)->comment('Payment amount');
            $table->string('currency', 3)->default('VND');
            $table->decimal('exchange_rate', 10, 6)->default(1)->comment('Exchange rate if different currency');
            $table->decimal('base_amount', 15, 2)->comment('Amount in base currency');
            
            // Processing fees
            $table->decimal('processing_fee', 15, 2)->default(0)->comment('Processing fee charged');
            $table->decimal('net_amount', 15, 2)->comment('Net amount after fees');
            
            // Payment status and processing
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded', 'partially_refunded'])->default('pending');
            $table->enum('verification_status', ['not_required', 'pending', 'verified', 'failed'])->default('not_required');
            $table->datetime('processed_at')->nullable();
            $table->datetime('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Provider transaction details
            $table->string('provider_transaction_id', 200)->nullable()->comment('Provider transaction ID');
            $table->string('provider_reference', 200)->nullable()->comment('Provider reference number');
            $table->json('provider_response')->nullable()->comment('Provider response data');
            $table->string('authorization_code', 100)->nullable()->comment('Authorization code');
            $table->string('receipt_number', 100)->nullable()->comment('Receipt number');
            
            // Card/Bank details (if applicable)
            $table->string('card_last_four', 4)->nullable()->comment('Last 4 digits of card');
            $table->string('card_type', 50)->nullable()->comment('Card type (Visa, MasterCard, etc.)');
            $table->string('bank_name', 200)->nullable()->comment('Bank name');
            $table->string('account_number_masked', 50)->nullable()->comment('Masked account number');
            
            // Refund tracking
            $table->decimal('refunded_amount', 15, 2)->default(0)->comment('Total refunded amount');
            $table->decimal('refundable_amount', 15, 2)->default(0)->comment('Remaining refundable amount');
            $table->integer('refund_count')->default(0)->comment('Number of refunds');
            $table->datetime('last_refund_date')->nullable();
            
            // Security and fraud detection
            $table->string('ip_address', 45)->nullable()->comment('Customer IP address');
            $table->string('user_agent', 500)->nullable()->comment('Customer user agent');
            $table->json('fraud_check_result')->nullable()->comment('Fraud detection results');
            $table->boolean('is_suspicious')->default(false)->comment('Flagged as suspicious');
            
            // Settlement tracking
            $table->boolean('is_settled')->default(false)->comment('Payment has been settled');
            $table->date('settlement_date')->nullable()->comment('Settlement date');
            $table->string('settlement_batch', 100)->nullable()->comment('Settlement batch ID');
            
            // Additional information
            $table->text('description')->nullable()->comment('Payment description');
            $table->text('notes')->nullable();
            $table->text('failure_reason')->nullable()->comment('Reason for failure');
            $table->json('metadata')->nullable()->comment('Additional payment data');
            
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'payment_number'], 'unique_store_payment_number');
            $table->index(['store_id', 'payment_method_id']);
            $table->index(['store_id', 'customer_id']);
            $table->index(['store_id', 'payment_date']);
            $table->index(['store_id', 'status']);
            $table->index(['order_number']);
            $table->index(['order_type', 'order_id']);
            $table->index(['payment_method_id', 'status']);
            $table->index(['customer_id', 'payment_date']);
            $table->index(['status', 'payment_date']);
            $table->index(['provider_transaction_id']);
            $table->index(['is_settled', 'settlement_date']);
            $table->index(['processed_by']);
            $table->index(['verified_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
