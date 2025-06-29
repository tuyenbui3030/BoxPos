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
        // Add store_id to customers table if not already exists
        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'store_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('store_id')->after('id')->constrained('stores')->onDelete('cascade');

                // Add indexes for better performance
                $table->index(['store_id']);
                $table->index(['store_id', 'customer_code']);
                $table->index(['store_id', 'email']);
            });
        }

        // Add store_id to other tables that need tenant isolation
        // You can add more tables here as needed

        // Example for products table (when created)
        /*
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'store_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('store_id')->after('id')->constrained('stores')->onDelete('cascade');
                $table->index(['store_id']);
                $table->index(['store_id', 'product_code']);
            });
        }
        */

        // Example for orders table (when created)
        /*
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'store_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('store_id')->after('id')->constrained('stores')->onDelete('cascade');
                $table->index(['store_id']);
                $table->index(['store_id', 'order_number']);
            });
        }
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove store_id from customers table
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'store_id')) {
            Schema::table('customers', function (Blueprint $table) {
                // Drop indexes first
                $table->dropIndex(['store_id', 'email']);
                $table->dropIndex(['store_id', 'customer_code']);
                $table->dropIndex(['store_id']);

                // Drop foreign key constraint
                $table->dropForeign(['store_id']);

                // Drop column
                $table->dropColumn('store_id');
            });
        }

        // Remove store_id from other tables
        // Add corresponding rollback for other tables here
    }
};
