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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['cash', 'bank_transfer', 'credit_card', 'debit_card', 'e_wallet', 'other']);
            $table->foreignId('account_id')->nullable()->constrained('cash_accounts')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'type']);

            // Unique constraint
            $table->unique(['store_id', 'name'], 'uk_payment_methods_store_name');
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('payment_terms')->nullable(); // Net 30, COD, etc.
            $table->string('tax_number')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('current_debt', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'code']);
            $table->index(['store_id', 'name']);

            // Unique constraints
            $table->unique(['store_id', 'code'], 'uk_suppliers_store_code');
            $table->unique(['store_id', 'email'], 'uk_suppliers_store_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('payment_methods');
    }
};