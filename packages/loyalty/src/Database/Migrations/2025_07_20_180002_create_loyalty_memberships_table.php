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
        Schema::create('loyalty_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('program_id')->constrained('loyalty_programs')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('membership_number', 100)->comment('Unique membership number');
            
            // Membership status
            $table->enum('status', ['active', 'inactive', 'suspended', 'expired'])->default('active');
            $table->date('enrolled_date')->comment('Date customer enrolled');
            $table->date('last_activity_date')->nullable()->comment('Last activity date');
            $table->date('expiry_date')->nullable()->comment('Membership expiry date');
            
            // Points balance
            $table->integer('total_points_earned')->default(0)->comment('Total points earned');
            $table->integer('total_points_redeemed')->default(0)->comment('Total points redeemed');
            $table->integer('current_points_balance')->default(0)->comment('Current points balance');
            $table->integer('pending_points')->default(0)->comment('Pending points (not yet available)');
            $table->integer('expired_points')->default(0)->comment('Total expired points');
            
            // Tier information
            $table->string('current_tier', 50)->nullable()->comment('Current tier level');
            $table->integer('tier_points')->default(0)->comment('Points in current tier');
            $table->integer('next_tier_points')->nullable()->comment('Points needed for next tier');
            $table->date('tier_achieved_date')->nullable()->comment('Date current tier was achieved');
            $table->date('tier_expiry_date')->nullable()->comment('Tier expiry date');
            
            // Activity tracking
            $table->integer('total_transactions')->default(0)->comment('Total transactions');
            $table->decimal('total_spent', 15, 2)->default(0)->comment('Total amount spent');
            $table->decimal('average_transaction', 15, 2)->default(0)->comment('Average transaction amount');
            $table->integer('visits_count')->default(0)->comment('Total visits/transactions');
            $table->date('first_purchase_date')->nullable();
            $table->date('last_purchase_date')->nullable();
            
            // Cashback tracking
            $table->decimal('total_cashback_earned', 15, 2)->default(0)->comment('Total cashback earned');
            $table->decimal('total_cashback_redeemed', 15, 2)->default(0)->comment('Total cashback redeemed');
            $table->decimal('current_cashback_balance', 15, 2)->default(0)->comment('Current cashback balance');
            
            // Referral tracking
            $table->integer('referrals_made')->default(0)->comment('Number of referrals made');
            $table->integer('referral_points_earned')->default(0)->comment('Points earned from referrals');
            $table->foreignId('referred_by')->nullable()->constrained('customers')->onDelete('set null');
            
            // Communication preferences
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('marketing_emails')->default(true);
            
            // Additional information
            $table->json('preferences')->nullable()->comment('Customer preferences');
            $table->json('metadata')->nullable()->comment('Additional membership data');
            
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'membership_number'], 'unique_store_membership_number');
            $table->unique(['program_id', 'customer_id'], 'unique_program_customer');
            $table->index(['store_id', 'program_id']);
            $table->index(['store_id', 'customer_id']);
            $table->index(['store_id', 'status']);
            $table->index(['program_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['current_tier']);
            $table->index(['last_activity_date']);
            $table->index(['referred_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_memberships');
    }
};
