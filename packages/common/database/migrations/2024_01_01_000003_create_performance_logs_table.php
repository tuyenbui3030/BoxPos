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
        Schema::create('performance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('operation');
            $table->decimal('duration', 10, 6); // Duration in seconds with microsecond precision
            $table->bigInteger('memory_usage'); // Memory usage in bytes
            $table->bigInteger('peak_memory'); // Peak memory usage in bytes
            $table->json('context')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index(['operation', 'created_at']);
            $table->index(['duration', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['store_id', 'created_at']);
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
        Schema::dropIfExists('performance_logs');
    }
};