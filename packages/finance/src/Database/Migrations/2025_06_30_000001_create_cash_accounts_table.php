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
        Schema::create('cash_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['cash', 'bank', 'credit_card', 'e_wallet', 'other'])->default('cash');
            $table->string('currency', 3)->default('VND');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'type']);
            $table->index(['store_id', 'branch_id']);

            // Unique constraint
            $table->unique(['store_id', 'name'], 'uk_cash_accounts_store_name');
        });

        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('cash_accounts')->onDelete('cascade');
            $table->date('transaction_date');
            $table->enum('type', ['income', 'expense', 'transfer_in', 'transfer_out']);
            $table->string('category')->nullable(); // sales, purchase, salary, rent, etc.
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('reference_type')->nullable(); // order, invoice, payroll, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes for performance and data cutoff
            $table->index(['store_id', 'transaction_date', 'type'], 'idx_cash_transactions_cutoff');
            $table->index(['store_id', 'account_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['category', 'transaction_date']);

            // Index for archiving
            $table->index(['transaction_date', 'store_id'], 'idx_cash_transactions_archive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
        Schema::dropIfExists('cash_accounts');
    }
};