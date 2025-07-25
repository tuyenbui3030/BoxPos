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
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('cash_accounts')->onDelete('restrict');
            $table->foreignId('category_id')->nullable()->constrained('cash_categories')->onDelete('set null');
            $table->string('transaction_number', 50)->comment('Transaction reference number');
            
            // Transaction details
            $table->date('transaction_date')->comment('Transaction date');
            $table->enum('type', ['income', 'expense', 'transfer_in', 'transfer_out'])->comment('Transaction type');
            $table->decimal('amount', 15, 2)->comment('Transaction amount');
            $table->string('currency', 3)->default('VND');
            $table->decimal('exchange_rate', 10, 6)->default(1)->comment('Exchange rate if different currency');
            $table->decimal('base_amount', 15, 2)->comment('Amount in base currency');
            
            // Transfer details (for transfer transactions)
            $table->foreignId('transfer_to_account_id')->nullable()->constrained('cash_accounts')->onDelete('set null');
            $table->foreignId('transfer_from_account_id')->nullable()->constrained('cash_accounts')->onDelete('set null');
            $table->string('transfer_reference', 100)->nullable()->comment('Transfer reference number');
            
            // Payment details
            $table->enum('payment_method', ['cash', 'bank_transfer', 'card', 'e_wallet', 'check', 'other'])->default('cash');
            $table->string('payment_reference', 100)->nullable()->comment('Payment reference (check number, transfer ID, etc.)');
            $table->string('payer_payee', 200)->nullable()->comment('Payer or payee name');
            $table->text('payer_payee_details')->nullable()->comment('Payer/payee contact details');
            
            // Related documents
            $table->string('related_document_type', 50)->nullable()->comment('invoice, purchase_order, sales_order, etc.');
            $table->unsignedBigInteger('related_document_id')->nullable()->comment('ID of related document');
            $table->string('related_document_number', 100)->nullable()->comment('Document number');
            
            // Transaction status
            $table->enum('status', ['pending', 'approved', 'completed', 'cancelled', 'failed'])->default('pending');
            $table->boolean('is_reconciled')->default(false)->comment('Bank reconciliation status');
            $table->date('reconciled_date')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Approval workflow
            $table->boolean('requires_approval')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Additional information
            $table->text('description')->nullable()->comment('Transaction description');
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable()->comment('Receipt/document attachments');
            $table->json('metadata')->nullable()->comment('Additional transaction data');
            
            // Audit trail
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'transaction_number'], 'unique_store_transaction_number');
            $table->index(['store_id', 'account_id']);
            $table->index(['store_id', 'category_id']);
            $table->index(['store_id', 'transaction_date']);
            $table->index(['store_id', 'type']);
            $table->index(['store_id', 'status']);
            $table->index(['account_id', 'transaction_date']);
            $table->index(['category_id', 'transaction_date']);
            $table->index(['type', 'transaction_date']);
            $table->index(['status']);
            $table->index(['is_reconciled']);
            $table->index(['requires_approval', 'is_approved']);
            $table->index(['related_document_type', 'related_document_id'], 'cash_transactions_related_doc_idx');
            $table->index(['created_by']);
            $table->index(['approved_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
