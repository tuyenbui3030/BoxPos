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
        Schema::create('supplier_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('material_suppliers')->onDelete('cascade');
            $table->string('name', 100)->comment('Contact person name');
            $table->string('position', 100)->nullable()->comment('Job position');
            $table->string('department', 100)->nullable()->comment('Department');
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('fax', 20)->nullable();
            $table->boolean('is_primary')->default(false)->comment('Primary contact flag');
            $table->boolean('is_active')->default(true);
            $table->json('responsibilities')->nullable()->comment('Contact responsibilities');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['supplier_id', 'is_primary']);
            $table->index(['supplier_id', 'is_active']);
            $table->index(['name']);
            $table->index(['email']);
            $table->index(['phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_contacts');
    }
};
