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
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('membership_id')->constrained('loyalty_memberships')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('transaction_number', 100)->comment('Loyalty transaction number');
            
            // Transaction details
            $table->enum('type', ['earn', 'redeem', 'expire', 'adjust', 'bonus', 'refund'])->comment('Transaction type');
            $table->enum('reason', [
                'purchase', 'return', 'bonus', 'referral', 'birthday', 'welcome', 
                'manual_adjust', 'expiry', 'redemption', 'cashback', 'tier_bonus'
            ])->comment('Transaction reason');
            $table->datetime('transaction_date')->comment('Transaction date');
            
            // Points/cashback amounts
            $table->integer('points_change')->default(0)->comment('Points change (+ for earn, - for redeem)');
            $table->integer('points_balance_before')->default(0)->comment('Points balance before transaction');
            $table->integer('points_balance_after')->default(0)->comment('Points balance after transaction');
            $table->decimal('cashback_change', 15, 2)->default(0)->comment('Cashback change');
            $table->decimal('cashback_balance_before', 15, 2)->default(0)->comment('Cashback balance before');
            $table->decimal('cashback_balance_after', 15, 2)->default(0)->comment('Cashback balance after');
            
            // Related order information
            $table->string('order_number', 100)->nullable()->comment('Related order number');
            $table->unsignedBigInteger('order_id')->nullable()->comment('Related order ID');
            $table->string('order_type', 50)->nullable()->comment('Order type (sales_order, invoice)');
            $table->decimal('order_amount', 15, 2)->default(0)->comment('Related order amount');
            
            // Earning details
            $table->decimal('earning_rate', 10, 4)->nullable()->comment('Rate used for earning');
            $table->decimal('multiplier', 5, 2)->default(1)->comment('Multiplier applied');
            $table->string('earning_rule', 200)->nullable()->comment('Rule that triggered earning');
            
            // Redemption details
            $table->decimal('redemption_rate', 10, 4)->nullable()->comment('Rate used for redemption');
            $table->decimal('redemption_value', 15, 2)->default(0)->comment('Value of redemption');
            $table->string('redemption_method', 50)->nullable()->comment('How points were redeemed');
            
            // Expiry tracking
            $table->date('points_expiry_date')->nullable()->comment('When these points expire');
            $table->boolean('is_expired')->default(false)->comment('Points have expired');
            $table->date('expired_date')->nullable()->comment('Date points expired');
            
            // Status and validation
            $table->enum('status', ['pending', 'completed', 'cancelled', 'expired'])->default('completed');
            $table->boolean('is_reversed')->default(false)->comment('Transaction has been reversed');
            $table->foreignId('reversed_by_transaction_id')->nullable()->constrained('loyalty_transactions')->onDelete('set null');
            $table->text('reversal_reason')->nullable();
            
            // Additional information
            $table->text('description')->nullable()->comment('Transaction description');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional transaction data');
            
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'transaction_number'], 'unique_store_loyalty_transaction_number');
            $table->index(['store_id', 'membership_id']);
            $table->index(['store_id', 'customer_id']);
            $table->index(['store_id', 'transaction_date']);
            $table->index(['membership_id', 'type']);
            $table->index(['membership_id', 'transaction_date']);
            $table->index(['customer_id', 'type']);
            $table->index(['type', 'reason']);
            $table->index(['order_number']);
            $table->index(['order_type', 'order_id']);
            $table->index(['status']);
            $table->index(['is_expired', 'points_expiry_date']);
            $table->index(['processed_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
