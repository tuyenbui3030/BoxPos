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
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('code', 100)->comment('Program code');
            $table->string('name', 200)->comment('Program name');
            $table->text('description')->nullable();
            
            // Program type and settings
            $table->enum('type', ['points', 'tiers', 'cashback', 'visits'])->default('points');
            $table->enum('status', ['draft', 'active', 'paused', 'ended'])->default('draft');
            $table->boolean('auto_enrollment')->default(true)->comment('Auto enroll new customers');
            
            // Points configuration
            $table->decimal('earn_rate', 10, 4)->default(1)->comment('Points earned per VND spent');
            $table->decimal('redeem_rate', 10, 4)->default(1)->comment('VND value per point');
            $table->integer('min_points_to_redeem')->default(100)->comment('Minimum points to redeem');
            $table->integer('max_points_per_transaction')->nullable()->comment('Max points per transaction');
            $table->integer('points_expiry_days')->nullable()->comment('Points expiry in days');
            
            // Tier configuration
            $table->json('tier_config')->nullable()->comment('Tier levels and requirements');
            $table->boolean('tier_based_earning')->default(false)->comment('Different earning rates per tier');
            $table->json('tier_benefits')->nullable()->comment('Benefits per tier');
            
            // Cashback configuration
            $table->decimal('cashback_rate', 5, 2)->default(0)->comment('Cashback percentage');
            $table->decimal('min_cashback_amount', 15, 2)->default(0)->comment('Minimum cashback amount');
            $table->decimal('max_cashback_amount', 15, 2)->nullable()->comment('Maximum cashback amount');
            
            // Visit-based configuration
            $table->integer('visits_for_reward')->default(10)->comment('Visits needed for reward');
            $table->decimal('visit_reward_amount', 15, 2)->default(0)->comment('Reward amount per milestone');
            
            // Enrollment and participation
            $table->date('start_date')->comment('Program start date');
            $table->date('end_date')->nullable()->comment('Program end date');
            $table->json('eligible_customer_groups')->nullable()->comment('Eligible customer groups');
            $table->decimal('min_purchase_amount', 15, 2)->default(0)->comment('Minimum purchase to earn');
            
            // Restrictions and rules
            $table->json('excluded_products')->nullable()->comment('Products excluded from earning');
            $table->json('excluded_categories')->nullable()->comment('Categories excluded from earning');
            $table->json('earning_channels')->nullable()->comment('Channels where points can be earned');
            $table->json('redemption_channels')->nullable()->comment('Channels where points can be redeemed');
            
            // Bonus and multipliers
            $table->json('bonus_events')->nullable()->comment('Special bonus earning events');
            $table->json('multiplier_rules')->nullable()->comment('Point multiplier rules');
            $table->boolean('birthday_bonus')->default(false)->comment('Birthday bonus enabled');
            $table->integer('birthday_bonus_points')->default(0)->comment('Birthday bonus points');
            
            // Communication and marketing
            $table->boolean('welcome_bonus')->default(false)->comment('Welcome bonus enabled');
            $table->integer('welcome_bonus_points')->default(0)->comment('Welcome bonus points');
            $table->boolean('referral_bonus')->default(false)->comment('Referral bonus enabled');
            $table->integer('referral_bonus_points')->default(0)->comment('Referral bonus points');
            $table->json('notification_settings')->nullable()->comment('Notification preferences');
            
            // Terms and conditions
            $table->text('terms_conditions')->nullable();
            $table->text('privacy_policy')->nullable();
            $table->json('metadata')->nullable()->comment('Additional program data');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'code'], 'unique_store_loyalty_program_code');
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'type']);
            $table->index(['status', 'start_date', 'end_date']);
            $table->index(['auto_enrollment']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_programs');
    }
};
