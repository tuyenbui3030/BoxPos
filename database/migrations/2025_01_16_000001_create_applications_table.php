<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 'ClinicPos', 'BuildingPos', etc.
            $table->string('slug')->unique(); // 'clinic-pos', 'building-pos'
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->json('color_scheme')->nullable(); // Brand colors
            $table->json('available_packages'); // Array of package names
            $table->json('pricing_tiers'); // Pricing structure
            $table->json('features')->nullable(); // Available features
            $table->enum('status', ['active', 'beta', 'coming_soon', 'deprecated'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};