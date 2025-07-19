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
        Schema::create('material_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('building_materials')->onDelete('cascade');
            $table->string('price_list_name', 100)->comment('Price list name (retail, wholesale, contractor, etc.)');
            $table->enum('customer_type', ['retail', 'wholesale', 'contractor', 'vip', 'staff'])->default('retail');
            
            // Quantity-based pricing
            $table->decimal('min_quantity', 15, 3)->default(1)->comment('Minimum quantity for this price');
            $table->decimal('max_quantity', 15, 3)->nullable()->comment('Maximum quantity for this price');
            $table->decimal('unit_price', 15, 2)->comment('Price per unit');
            $table->decimal('discount_percentage', 5, 2)->default(0)->comment('Discount percentage');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Fixed discount amount');
            
            // Time-based pricing
            $table->date('effective_from')->comment('Price effective from date');
            $table->date('effective_to')->nullable()->comment('Price effective to date');
            $table->enum('season', ['all_year', 'dry_season', 'rainy_season', 'peak_season', 'off_season'])->default('all_year');
            
            // Location-based pricing
            $table->json('delivery_areas')->nullable()->comment('Applicable delivery areas');
            $table->decimal('delivery_surcharge', 15, 2)->default(0)->comment('Delivery surcharge');
            $table->boolean('free_delivery')->default(false)->comment('Free delivery flag');
            $table->decimal('free_delivery_threshold', 15, 2)->nullable()->comment('Minimum order for free delivery');
            
            // Currency and tax
            $table->string('currency', 3)->default('VND');
            $table->decimal('tax_rate', 5, 2)->default(0)->comment('Tax rate percentage');
            $table->boolean('tax_inclusive')->default(false)->comment('Price includes tax');
            
            // Status and metadata
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false)->comment('Default price for this customer type');
            $table->integer('priority')->default(0)->comment('Price priority (higher = more priority)');
            $table->text('notes')->nullable();
            $table->json('conditions')->nullable()->comment('Additional pricing conditions');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'material_id', 'customer_type'], 'idx_pricing_store_material_customer');
            $table->index(['store_id', 'material_id', 'effective_from', 'effective_to'], 'idx_pricing_store_material_dates');
            $table->index(['material_id', 'min_quantity', 'max_quantity'], 'idx_pricing_material_quantity');
            $table->index(['material_id', 'is_active'], 'idx_pricing_material_active');
            $table->index(['material_id', 'is_default'], 'idx_pricing_material_default');
            $table->index(['material_id', 'priority'], 'idx_pricing_material_priority');
            $table->index(['effective_from', 'effective_to'], 'idx_pricing_dates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_pricing');
    }
};
