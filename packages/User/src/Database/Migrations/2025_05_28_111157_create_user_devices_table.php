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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_name')->nullable();
            $table->string('device_type')->nullable(); // mobile, desktop, tablet
            $table->string('browser')->nullable();
            $table->string('platform')->nullable(); // Windows, macOS, Linux, Android, iOS
            $table->string('ip_address');
            $table->text('user_agent');
            $table->string('remember_token')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_trusted')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'remember_token']);
            $table->index(['user_id', 'last_activity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
