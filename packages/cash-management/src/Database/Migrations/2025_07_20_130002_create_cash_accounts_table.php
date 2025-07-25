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
            $table->string('account_code', 50)->comment('Account code');
            $table->string('account_name', 200)->comment('Account name');
            $table->text('description')->nullable();
            $table->enum('account_type', ['cash', 'bank', 'e_wallet', 'credit_card', 'petty_cash'])->comment('Account type');
            $table->string('currency', 3)->default('VND');
            
            // Account details
            $table->string('bank_name', 200)->nullable()->comment('Bank name for bank accounts');
            $table->string('account_number', 50)->nullable()->comment('Bank account number');
            $table->string('account_holder', 200)->nullable()->comment('Account holder name');
            $table->string('branch', 200)->nullable()->comment('Bank branch');
            $table->string('swift_code', 20)->nullable()->comment('SWIFT/BIC code');
            
            // Balance tracking
            $table->decimal('opening_balance', 15, 2)->default(0)->comment('Opening balance');
            $table->decimal('current_balance', 15, 2)->default(0)->comment('Current balance');
            $table->date('opening_date')->comment('Account opening date');
            
            // Limits and controls
            $table->decimal('credit_limit', 15, 2)->default(0)->comment('Credit limit for credit accounts');
            $table->decimal('daily_limit', 15, 2)->default(0)->comment('Daily transaction limit');
            $table->decimal('monthly_limit', 15, 2)->default(0)->comment('Monthly transaction limit');
            $table->boolean('require_approval')->default(false)->comment('Require approval for transactions');
            $table->decimal('approval_threshold', 15, 2)->default(0)->comment('Amount threshold for approval');
            
            // Status and settings
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false)->comment('Default account for transactions');
            $table->boolean('allow_negative')->default(false)->comment('Allow negative balance');
            $table->boolean('is_reconciled')->default(true)->comment('Account reconciliation status');
            $table->date('last_reconciled_date')->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional account data');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'account_code'], 'unique_store_account_code');
            $table->index(['store_id', 'account_type']);
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'is_default']);
            $table->index(['account_type']);
            $table->index(['currency']);
            $table->index(['is_active']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_accounts');
    }
};
