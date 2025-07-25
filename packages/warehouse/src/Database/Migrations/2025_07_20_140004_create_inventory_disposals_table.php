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
        Schema::create('inventory_disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('disposal_number', 50)->comment('Disposal reference number');
            $table->string('title', 200)->comment('Disposal title/name');
            $table->text('description')->nullable();
            
            // Disposal details
            $table->date('disposal_date')->comment('Disposal date');
            $table->enum('disposal_type', ['damage', 'expired', 'obsolete', 'theft', 'loss', 'quality_issue', 'other'])->comment('Disposal type');
            $table->enum('disposal_method', ['destroy', 'sell', 'donate', 'return_supplier', 'recycle', 'other'])->comment('Disposal method');
            $table->enum('status', ['draft', 'pending', 'approved', 'processed', 'completed', 'cancelled'])->default('draft');
            
            // Financial impact
            $table->decimal('total_cost_value', 15, 2)->default(0)->comment('Total cost value of disposed items');
            $table->decimal('recovery_value', 15, 2)->default(0)->comment('Value recovered from disposal');
            $table->decimal('net_loss', 15, 2)->default(0)->comment('Net loss from disposal');
            $table->string('currency', 3)->default('VND');
            
            // Disposal location and method details
            $table->string('disposal_location', 200)->nullable()->comment('Where disposal took place');
            $table->string('disposal_company', 200)->nullable()->comment('Company handling disposal');
            $table->string('disposal_certificate', 100)->nullable()->comment('Disposal certificate number');
            $table->date('disposal_certificate_date')->nullable();
            
            // Approval workflow
            $table->boolean('requires_approval')->default(true);
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Processing details
            $table->boolean('inventory_adjusted')->default(false)->comment('Inventory has been adjusted');
            $table->timestamp('inventory_adjusted_at')->nullable();
            $table->foreignId('inventory_adjusted_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Insurance and claims
            $table->boolean('insurance_claim')->default(false)->comment('Insurance claim filed');
            $table->string('insurance_claim_number', 100)->nullable();
            $table->decimal('insurance_claim_amount', 15, 2)->default(0);
            $table->enum('insurance_claim_status', ['pending', 'approved', 'rejected', 'paid'])->nullable();
            
            // Documentation
            $table->json('attachments')->nullable()->comment('Photos, documents, certificates');
            $table->text('disposal_notes')->nullable();
            $table->text('investigation_notes')->nullable()->comment('Investigation details if applicable');
            
            // Additional information
            $table->json('metadata')->nullable()->comment('Additional disposal data');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'disposal_number'], 'unique_store_disposal_number');
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'disposal_date']);
            $table->index(['store_id', 'disposal_type']);
            $table->index(['disposal_type', 'disposal_date']);
            $table->index(['status']);
            $table->index(['requires_approval', 'is_approved']);
            $table->index(['insurance_claim']);
            $table->index(['created_by']);
            $table->index(['approved_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_disposals');
    }
};
