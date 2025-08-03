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
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('error_type');
            $table->text('error_message');
            $table->integer('error_code')->nullable();
            $table->string('file');
            $table->integer('line');
            $table->longText('stack_trace');
            $table->json('context')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index(['error_type', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['store_id', 'created_at']);
            $table->index(['file', 'line']);
            $table->index('created_at');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};