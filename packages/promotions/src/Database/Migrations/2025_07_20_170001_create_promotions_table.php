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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('code', 100)->comment('Promotion code');
            $table->string('name', 200)->comment('Promotion name');
            $table->text('description')->nullable();
            
            // Promotion type and classification
            $table->enum('type', ['percentage', 'fixed_amount', 'buy_x_get_y', 'bundle', 'shipping', 'loyalty_points'])->comment('Promotion type');
            $table->enum('target', ['order', 'product', 'category', 'customer', 'shipping'])->comment('Promotion target');
            $table->enum('status', ['draft', 'active', 'paused', 'expired', 'cancelled'])->default('draft');
            
            // Discount configuration
            $table->decimal('discount_value', 15, 2)->default(0)->comment('Discount value (percentage or amount)');
            $table->decimal('max_discount_amount', 15, 2)->nullable()->comment('Maximum discount amount for percentage');
            $table->decimal('min_order_amount', 15, 2)->default(0)->comment('Minimum order amount');
            $table->decimal('max_order_amount', 15, 2)->nullable()->comment('Maximum order amount');
            
            // Buy X Get Y configuration
            $table->integer('buy_quantity')->nullable()->comment('Buy X quantity');
            $table->integer('get_quantity')->nullable()->comment('Get Y quantity');
            $table->decimal('get_discount_percent', 5, 2)->default(0)->comment('Discount on Y items');
            $table->boolean('get_free')->default(false)->comment('Get Y items free');
            
            // Usage limits
            $table->integer('usage_limit')->nullable()->comment('Total usage limit');
            $table->integer('usage_limit_per_customer')->nullable()->comment('Usage limit per customer');
            $table->integer('current_usage')->default(0)->comment('Current usage count');
            
            // Date and time restrictions
            $table->datetime('start_date')->comment('Promotion start date');
            $table->datetime('end_date')->comment('Promotion end date');
            $table->json('time_restrictions')->nullable()->comment('Time of day restrictions');
            $table->json('day_restrictions')->nullable()->comment('Day of week restrictions');
            
            // Customer restrictions
            $table->json('customer_groups')->nullable()->comment('Allowed customer groups');
            $table->json('customer_ids')->nullable()->comment('Specific customer IDs');
            $table->boolean('new_customers_only')->default(false);
            $table->boolean('existing_customers_only')->default(false);
            
            // Product/Category restrictions
            $table->json('included_products')->nullable()->comment('Included product IDs');
            $table->json('excluded_products')->nullable()->comment('Excluded product IDs');
            $table->json('included_categories')->nullable()->comment('Included category IDs');
            $table->json('excluded_categories')->nullable()->comment('Excluded category IDs');
            
            // Channel restrictions
            $table->json('sales_channels')->nullable()->comment('Allowed sales channels');
            $table->json('payment_methods')->nullable()->comment('Allowed payment methods');
            
            // Combination rules
            $table->boolean('combinable')->default(false)->comment('Can be combined with other promotions');
            $table->json('combinable_with')->nullable()->comment('Specific promotions it can combine with');
            $table->integer('priority')->default(0)->comment('Priority when multiple promotions apply');
            
            // Display and marketing
            $table->string('display_name', 200)->nullable()->comment('Display name for customers');
            $table->text('terms_conditions')->nullable();
            $table->string('banner_image', 500)->nullable();
            $table->boolean('show_on_website')->default(true);
            $table->boolean('show_in_app')->default(true);
            
            // Tracking and analytics
            $table->decimal('total_discount_given', 15, 2)->default(0)->comment('Total discount amount given');
            $table->integer('total_orders_affected')->default(0)->comment('Total orders affected');
            $table->decimal('total_revenue_impact', 15, 2)->default(0)->comment('Total revenue impact');
            
            // Additional settings
            $table->json('metadata')->nullable()->comment('Additional promotion data');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'code'], 'unique_store_promotion_code');
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'type']);
            $table->index(['store_id', 'target']);
            $table->index(['status', 'start_date', 'end_date']);
            $table->index(['start_date', 'end_date']);
            $table->index(['priority']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
