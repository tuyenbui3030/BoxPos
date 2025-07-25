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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->string('code', 50)->comment('Position code');
            $table->string('title', 200)->comment('Position title');
            $table->text('description')->nullable();
            $table->text('responsibilities')->nullable()->comment('Job responsibilities');
            $table->text('requirements')->nullable()->comment('Job requirements');
            $table->enum('level', ['entry', 'junior', 'senior', 'lead', 'manager', 'director', 'executive'])->default('entry');
            $table->decimal('min_salary', 15, 2)->default(0)->comment('Minimum salary');
            $table->decimal('max_salary', 15, 2)->default(0)->comment('Maximum salary');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable()->comment('Additional position data');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'code'], 'unique_store_position_code');
            $table->index(['store_id', 'department_id']);
            $table->index(['store_id', 'is_active']);
            $table->index(['department_id']);
            $table->index(['level']);
            $table->index(['sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
