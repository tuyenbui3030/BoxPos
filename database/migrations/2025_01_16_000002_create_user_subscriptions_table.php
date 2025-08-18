<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->enum('plan_type', ['trial', 'basic', 'professional', 'enterprise'])->default('trial');
            $table->enum('status', ['active', 'suspended', 'cancelled', 'expired'])->default('active');
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->nullable();
            $table->integer('max_stores')->default(1);
            $table->json('features')->nullable(); // Enabled features for this subscription
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->json('billing_info')->nullable(); // Payment method, billing cycle, etc.
            $table->timestamp('last_billed_at')->nullable();
            $table->timestamp('next_billing_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'application_id']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};