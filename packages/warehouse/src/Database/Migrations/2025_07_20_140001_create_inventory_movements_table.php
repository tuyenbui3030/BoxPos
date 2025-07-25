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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('building_materials')->onDelete('restrict');
            $table->string('movement_number', 50)->comment('Movement reference number');
            
            // Movement details
            $table->date('movement_date')->comment('Movement date');
            $table->enum('movement_type', ['in', 'out', 'adjustment', 'transfer', 'disposal', 'return'])->comment('Movement type');
            $table->enum('movement_reason', [
                'purchase', 'sale', 'return', 'adjustment', 'transfer_in', 'transfer_out', 
                'disposal', 'damage', 'expired', 'theft', 'production', 'sample', 'other'
            ])->comment('Reason for movement');
            
            // Quantity details
            $table->decimal('quantity', 15, 3)->comment('Movement quantity');
            $table->string('unit', 50)->comment('Unit of measurement');
            $table->decimal('unit_cost', 15, 2)->default(0)->comment('Unit cost at time of movement');
            $table->decimal('total_cost', 15, 2)->default(0)->comment('Total cost of movement');
            
            // Before/After balances
            $table->decimal('balance_before', 15, 3)->default(0)->comment('Stock balance before movement');
            $table->decimal('balance_after', 15, 3)->default(0)->comment('Stock balance after movement');
            
            // Batch/Serial tracking
            $table->string('batch_number', 100)->nullable()->comment('Batch/lot number');
            $table->string('serial_numbers', 1000)->nullable()->comment('Serial numbers (JSON array)');
            $table->date('expiry_date')->nullable();
            
            // Related documents
            $table->string('related_document_type', 50)->nullable()->comment('purchase_order, sales_order, invoice, etc.');
            $table->unsignedBigInteger('related_document_id')->nullable()->comment('ID of related document');
            $table->string('related_document_number', 100)->nullable()->comment('Document number');
            
            // Location tracking
            $table->string('from_location', 200)->nullable()->comment('Source location');
            $table->string('to_location', 200)->nullable()->comment('Destination location');
            $table->string('warehouse_zone', 100)->nullable()->comment('Warehouse zone/area');
            
            // Approval and verification
            $table->boolean('requires_approval')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_verified')->default(false)->comment('Physical verification completed');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            
            // Additional information
            $table->text('description')->nullable()->comment('Movement description');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional movement data');
            
            // Audit trail
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'movement_number'], 'unique_store_movement_number');
            $table->index(['store_id', 'material_id']);
            $table->index(['store_id', 'movement_date']);
            $table->index(['store_id', 'movement_type']);
            $table->index(['store_id', 'movement_reason']);
            $table->index(['material_id', 'movement_date']);
            $table->index(['movement_type', 'movement_date']);
            $table->index(['batch_number']);
            $table->index(['related_document_type', 'related_document_id'], 'inventory_movements_related_doc_idx');
            $table->index(['requires_approval', 'is_approved']);
            $table->index(['is_verified']);
            $table->index(['created_by']);
            $table->index(['approved_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
