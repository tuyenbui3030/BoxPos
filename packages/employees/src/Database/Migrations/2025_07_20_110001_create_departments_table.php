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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('departments')->onDelete('cascade');
            $table->string('code', 50)->comment('Department code');
            $table->string('name', 200)->comment('Department name');
            $table->text('description')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('location', 200)->nullable()->comment('Department location');
            $table->decimal('budget', 15, 2)->default(0)->comment('Department budget');
            $table->integer('max_employees')->nullable()->comment('Maximum number of employees');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable()->comment('Additional department data');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'code'], 'unique_store_department_code');
            $table->index(['store_id', 'parent_id']);
            $table->index(['store_id', 'is_active']);
            $table->index(['manager_id']);
            $table->index(['sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
