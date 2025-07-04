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
        Schema::create('disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->string('reference_number')->unique();
            $table->date('disposal_date');
            $table->enum('reason', ['expired', 'damaged', 'obsolete', 'quality_issue', 'other']);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'status', 'disposal_date']);
            $table->index(['store_id', 'reference_number']);
            $table->index(['reason', 'disposal_date']);
        });

        Schema::create('disposal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disposal_id')->constrained('disposals')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->decimal('quantity', 15, 3);
            $table->decimal('cost_price', 15, 2);
            $table->decimal('total_cost', 15, 2)->storedAs('quantity * cost_price');
            $table->enum('reason', ['expired', 'damaged', 'obsolete', 'quality_issue', 'other']);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['disposal_id', 'product_id', 'variant_id']);
            $table->index(['reason']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposal_items');
        Schema::dropIfExists('disposals');
    }
};