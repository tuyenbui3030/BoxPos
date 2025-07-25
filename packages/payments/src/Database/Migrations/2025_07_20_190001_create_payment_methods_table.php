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
            $table->string('code', 100)->comment('Payment method code');
            $table->string('name', 200)->comment('Payment method name');
            $table->text('description')->nullable();
            
            // Payment method type and classification
            $table->enum('type', ['cash', 'card', 'bank_transfer', 'e_wallet', 'qr_code', 'crypto', 'credit', 'other'])->comment('Payment method type');
            $table->enum('category', ['instant', 'delayed', 'credit'])->default('instant')->comment('Payment category');
            $table->boolean('requires_verification')->default(false)->comment('Requires payment verification');
            
            // Provider information
            $table->string('provider', 200)->nullable()->comment('Payment provider (Visa, MasterCard, Momo, etc.)');
            $table->string('provider_code', 100)->nullable()->comment('Provider specific code');
            $table->json('provider_config')->nullable()->comment('Provider configuration');
            
            // Processing settings
            $table->decimal('processing_fee_percent', 5, 4)->default(0)->comment('Processing fee percentage');
            $table->decimal('processing_fee_fixed', 15, 2)->default(0)->comment('Fixed processing fee');
            $table->decimal('min_amount', 15, 2)->default(0)->comment('Minimum transaction amount');
            $table->decimal('max_amount', 15, 2)->nullable()->comment('Maximum transaction amount');
            $table->string('currency', 3)->default('VND');
            
            // Settlement and timing
            $table->integer('settlement_days')->default(0)->comment('Days for settlement');
            $table->time('cutoff_time')->nullable()->comment('Daily cutoff time');
            $table->json('processing_days')->nullable()->comment('Days when processing occurs');
            
            // Integration settings
            $table->string('api_endpoint', 500)->nullable()->comment('API endpoint for integration');
            $table->string('webhook_url', 500)->nullable()->comment('Webhook URL for notifications');
            $table->json('api_credentials')->nullable()->comment('Encrypted API credentials');
            $table->boolean('sandbox_mode')->default(false)->comment('Sandbox/test mode');
            
            // Display and UI settings
            $table->string('icon', 500)->nullable()->comment('Payment method icon URL');
            $table->string('color', 7)->nullable()->comment('Brand color');
            $table->boolean('show_on_pos')->default(true)->comment('Show on POS');
            $table->boolean('show_on_website')->default(true)->comment('Show on website');
            $table->boolean('show_on_mobile')->default(true)->comment('Show on mobile app');
            $table->integer('sort_order')->default(0);
            
            // Status and availability
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false)->comment('Default payment method');
            $table->json('availability_schedule')->nullable()->comment('When payment method is available');
            $table->text('unavailable_message')->nullable()->comment('Message when unavailable');
            
            // Security and compliance
            $table->boolean('requires_pin')->default(false)->comment('Requires PIN entry');
            $table->boolean('requires_signature')->default(false)->comment('Requires signature');
            $table->boolean('supports_refund')->default(true)->comment('Supports refunds');
            $table->boolean('supports_partial_refund')->default(true)->comment('Supports partial refunds');
            $table->integer('refund_days_limit')->nullable()->comment('Days limit for refunds');
            
            // Additional settings
            $table->json('metadata')->nullable()->comment('Additional payment method data');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'code'], 'unique_store_payment_method_code');
            $table->index(['store_id', 'type']);
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'is_default']);
            $table->index(['type', 'is_active']);
            $table->index(['provider']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
