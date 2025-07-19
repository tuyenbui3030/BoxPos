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
        Schema::create('material_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('building_materials')->onDelete('cascade');
            $table->string('spec_name', 100)->comment('Specification name');
            $table->string('spec_value', 500)->comment('Specification value');
            $table->string('spec_unit', 50)->nullable()->comment('Unit of measurement');
            $table->enum('spec_type', ['text', 'number', 'boolean', 'date', 'json'])->default('text');
            $table->string('spec_category', 100)->nullable()->comment('Specification category');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(false)->comment('Required for quality check');
            $table->boolean('is_searchable')->default(false)->comment('Include in search');
            $table->boolean('show_in_listing')->default(false)->comment('Show in product listing');
            $table->timestamps();

            // Indexes
            $table->index(['material_id', 'spec_name']);
            $table->index(['material_id', 'spec_category']);
            $table->index(['material_id', 'is_required']);
            $table->index(['material_id', 'is_searchable']);
            $table->index(['material_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_specifications');
    }
};
