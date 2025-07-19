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
        Schema::create('material_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('supplier_code', 50)->comment('Supplier code');
            $table->string('company_name', 200)->comment('Company name');
            $table->string('contact_person', 100)->nullable()->comment('Contact person name');
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 200)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->default('Vietnam');
            $table->string('tax_code', 50)->nullable()->comment('Business tax code');
            $table->enum('supplier_type', ['manufacturer', 'distributor', 'retailer', 'importer'])->default('distributor');
            $table->enum('payment_terms', ['cash', 'cod', 'net_15', 'net_30', 'net_60', 'net_90'])->default('net_30');
            $table->decimal('credit_limit', 15, 2)->default(0)->comment('Credit limit amount');
            $table->decimal('current_balance', 15, 2)->default(0)->comment('Current outstanding balance');
            $table->integer('lead_time_days')->default(7)->comment('Default lead time in days');
            $table->decimal('rating', 3, 2)->default(0)->comment('Supplier rating (0-5)');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_preferred')->default(false)->comment('Preferred supplier flag');
            $table->json('certifications')->nullable()->comment('Quality certifications');
            $table->json('delivery_areas')->nullable()->comment('Delivery coverage areas');
            $table->text('notes')->nullable();
            $table->timestamp('last_order_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'supplier_code'], 'unique_store_supplier_code');
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'is_preferred']);
            $table->index(['store_id', 'supplier_type']);
            $table->index(['store_id', 'rating']);
            $table->index(['company_name']);
            $table->index(['phone']);
            $table->index(['email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_suppliers');
    }
};
