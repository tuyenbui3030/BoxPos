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
        // Daily Sales Summary for performance
        Schema::create('daily_sales_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->date('date');
            $table->integer('total_orders')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('total_profit', 15, 2)->default(0);
            $table->decimal('avg_order_value', 15, 2)->default(0);
            $table->unsignedBigInteger('top_product_id')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['store_id', 'date']);
            $table->index(['store_id', 'branch_id', 'date']);
            $table->index(['date', 'store_id']); // For data cutoff

            // Unique constraint
            $table->unique(['store_id', 'branch_id', 'date'], 'uk_daily_sales_store_branch_date');
        });

        // Product Performance Cache
        Schema::create('product_performance_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->unsignedBigInteger('product_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('quantity_sold')->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->integer('rank')->nullable();
            $table->timestamp('last_updated')->useCurrent();
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'period_start', 'period_end']);
            $table->index(['store_id', 'product_id', 'period_start']);
            $table->index(['rank', 'store_id']);
        });

        // Customer Analytics Cache
        Schema::create('customer_analytics_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->unsignedBigInteger('customer_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->decimal('avg_order_value', 15, 2)->default(0);
            $table->date('last_order_date')->nullable();
            $table->timestamp('last_updated')->useCurrent();
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'customer_id', 'period_start']);
            $table->index(['store_id', 'period_start', 'period_end']);
            $table->index(['total_spent', 'store_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_analytics_cache');
        Schema::dropIfExists('product_performance_cache');
        Schema::dropIfExists('daily_sales_summary');
    }
};