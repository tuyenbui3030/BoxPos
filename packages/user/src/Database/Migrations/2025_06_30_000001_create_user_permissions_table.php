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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('permissions'); // Array of permission keys
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'name']);

            // Unique constraint
            $table->unique(['store_id', 'name'], 'uk_roles_store_name');
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'store_id']);
            $table->index(['store_id', 'role_id']);

            // Unique constraint
            $table->unique(['user_id', 'store_id', 'role_id'], 'uk_user_roles_unique');
        });

        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->ipAddress('ip_address');
            $table->text('user_agent');
            $table->timestamp('login_at');
            $table->timestamp('last_activity');
            $table->timestamp('logout_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'is_active']);
            $table->index(['store_id', 'login_at']);
            $table->index(['session_id']);
            $table->index(['last_activity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
    }
};