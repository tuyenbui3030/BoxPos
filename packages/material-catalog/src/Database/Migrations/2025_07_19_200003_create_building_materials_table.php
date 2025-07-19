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
        Schema::create('building_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('material_categories')->onDelete('restrict');
            $table->foreignId('primary_unit_id')->constrained('material_units')->onDelete('restrict');
            $table->string('material_code', 100)->comment('Material SKU/Code');
            $table->string('name', 300)->comment('Material name');
            $table->string('slug', 350)->comment('URL-friendly slug');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('brand', 100)->nullable()->comment('Material brand');
            $table->string('model', 100)->nullable()->comment('Model/Grade');
            $table->string('origin_country', 100)->nullable()->comment('Country of origin');
            $table->json('images')->nullable()->comment('Material images');
            $table->string('barcode', 50)->nullable();
            $table->string('qr_code', 200)->nullable();
            
            // Basic info
            $table->decimal('weight_per_unit', 10, 3)->nullable()->comment('Weight per unit');
            $table->json('dimensions')->nullable()->comment('Physical dimensions');
            $table->boolean('is_hazardous')->default(false)->comment('Hazardous material flag');
            $table->json('storage_requirements')->nullable()->comment('Storage conditions');
            
            // Quality & Compliance
            $table->json('quality_standards')->nullable()->comment('Quality standards (TCVN, ISO, etc.)');
            $table->json('certifications')->nullable()->comment('Quality certifications');
            $table->date('expiry_date')->nullable()->comment('Material expiry date');
            $table->integer('shelf_life_days')->nullable()->comment('Shelf life in days');
            $table->boolean('requires_quality_check')->default(false);
            
            // Status & Flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('track_serial_numbers')->default(false);
            $table->boolean('track_batch_numbers')->default(false);
            
            // Metadata
            $table->json('technical_specs')->nullable()->comment('Technical specifications');
            $table->json('tags')->nullable()->comment('Search tags');
            $table->text('internal_notes')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'material_code'], 'unique_store_material_code');
            $table->unique(['store_id', 'slug'], 'unique_store_material_slug');
            $table->index(['store_id', 'category_id']);
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'is_featured']);
            $table->index(['store_id', 'brand']);
            $table->index(['name']);
            $table->index(['barcode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_materials');
    }
};
