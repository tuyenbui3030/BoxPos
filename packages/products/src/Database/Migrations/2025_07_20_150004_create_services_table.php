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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('set null');
            $table->string('code', 100)->comment('Service code');
            $table->string('name', 300)->comment('Service name');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            
            // Service type and classification
            $table->enum('type', ['standard', 'custom', 'subscription', 'one_time'])->default('standard');
            $table->enum('status', ['active', 'inactive', 'draft', 'discontinued'])->default('active');
            $table->enum('billing_type', ['fixed', 'hourly', 'daily', 'monthly', 'custom'])->default('fixed');
            
            // Pricing
            $table->decimal('base_price', 15, 2)->default(0)->comment('Base service price');
            $table->decimal('hourly_rate', 15, 2)->nullable()->comment('Hourly rate for time-based services');
            $table->decimal('setup_fee', 15, 2)->default(0)->comment('One-time setup fee');
            $table->string('currency', 3)->default('VND');
            
            // Duration and scheduling
            $table->integer('estimated_duration')->nullable()->comment('Estimated duration in minutes');
            $table->integer('min_duration')->nullable()->comment('Minimum duration in minutes');
            $table->integer('max_duration')->nullable()->comment('Maximum duration in minutes');
            $table->boolean('requires_booking')->default(false)->comment('Requires advance booking');
            $table->integer('booking_lead_time')->default(0)->comment('Minimum lead time for booking in hours');
            $table->json('available_days')->nullable()->comment('Available days of week');
            $table->time('available_from')->nullable()->comment('Available from time');
            $table->time('available_to')->nullable()->comment('Available to time');
            
            // Tax settings
            $table->boolean('is_taxable')->default(true);
            $table->decimal('tax_rate', 5, 2)->default(0)->comment('Tax rate percentage');
            $table->string('tax_class', 50)->nullable()->comment('Tax class');
            
            // Service delivery
            $table->enum('delivery_method', ['in_person', 'remote', 'hybrid', 'on_site'])->default('in_person');
            $table->string('location', 200)->nullable()->comment('Service location');
            $table->boolean('requires_materials')->default(false)->comment('Requires materials/products');
            $table->json('required_materials')->nullable()->comment('List of required materials');
            
            // Staff and resources
            $table->boolean('requires_staff')->default(true);
            $table->json('required_skills')->nullable()->comment('Required staff skills');
            $table->integer('min_staff')->default(1)->comment('Minimum staff required');
            $table->integer('max_staff')->nullable()->comment('Maximum staff allowed');
            
            // Media and marketing
            $table->json('images')->nullable()->comment('Service images');
            $table->string('featured_image', 500)->nullable();
            $table->string('meta_title', 200)->nullable();
            $table->text('meta_description')->nullable();
            $table->json('tags')->nullable()->comment('Service tags');
            
            // Additional settings
            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_online_booking')->default(false);
            $table->boolean('send_confirmation')->default(true);
            $table->boolean('send_reminder')->default(true);
            $table->integer('reminder_hours')->default(24)->comment('Hours before service to send reminder');
            $table->integer('sort_order')->default(0);
            $table->json('custom_fields')->nullable()->comment('Custom service fields');
            $table->json('metadata')->nullable()->comment('Additional service data');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'code'], 'unique_store_service_code');
            $table->index(['store_id', 'category_id']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'type']);
            $table->index(['name']);
            $table->index(['is_featured']);
            $table->index(['requires_booking']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
