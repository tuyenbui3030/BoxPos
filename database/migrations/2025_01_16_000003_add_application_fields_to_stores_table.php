<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->foreignId('application_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('subscription_id')->nullable()->constrained('user_subscriptions')->onDelete('restrict');
            $table->json('app_settings')->nullable(); // App-specific settings
            $table->json('custom_branding')->nullable(); // White-label branding
            $table->boolean('is_demo')->default(false); // Demo store flag
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign(['application_id']);
            $table->dropForeign(['subscription_id']);
            $table->dropColumn(['application_id', 'subscription_id', 'app_settings', 'custom_branding', 'is_demo']);
        });
    }
};