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
        Schema::create('material_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('code', 20)->comment('Unit code (kg, ton, m3, etc.)');
            $table->string('name', 100)->comment('Unit display name');
            $table->string('symbol', 10)->nullable()->comment('Unit symbol');
            $table->enum('type', ['weight', 'volume', 'area', 'length', 'count'])->comment('Unit category');
            $table->decimal('conversion_factor', 10, 4)->default(1)->comment('Conversion factor to base unit');
            $table->string('base_unit_code', 20)->nullable()->comment('Base unit for conversion');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false)->comment('Default unit for this type');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'code'], 'unique_store_unit_code');
            $table->index(['store_id', 'type']);
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_units');
    }
};
