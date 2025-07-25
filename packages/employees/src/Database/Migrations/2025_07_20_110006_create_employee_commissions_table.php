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
        Schema::create('employee_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('commission_period', 20)->comment('Commission period (monthly, quarterly, yearly)');
            $table->date('period_start_date')->comment('Period start date');
            $table->date('period_end_date')->comment('Period end date');
            
            // Sales performance
            $table->decimal('total_sales', 15, 2)->default(0)->comment('Total sales amount');
            $table->decimal('target_sales', 15, 2)->default(0)->comment('Target sales amount');
            $table->decimal('achievement_percentage', 5, 2)->default(0)->comment('Achievement percentage');
            $table->integer('total_orders')->default(0)->comment('Total number of orders');
            $table->integer('target_orders')->default(0)->comment('Target number of orders');
            
            // Commission calculation
            $table->decimal('base_commission_rate', 5, 2)->default(0)->comment('Base commission rate percentage');
            $table->decimal('bonus_commission_rate', 5, 2)->default(0)->comment('Bonus commission rate percentage');
            $table->decimal('base_commission_amount', 15, 2)->default(0)->comment('Base commission amount');
            $table->decimal('bonus_commission_amount', 15, 2)->default(0)->comment('Bonus commission amount');
            $table->decimal('total_commission', 15, 2)->default(0)->comment('Total commission amount');
            
            // Deductions and adjustments
            $table->decimal('deductions', 15, 2)->default(0)->comment('Commission deductions');
            $table->decimal('adjustments', 15, 2)->default(0)->comment('Commission adjustments');
            $table->text('deduction_reason')->nullable()->comment('Reason for deductions');
            $table->text('adjustment_reason')->nullable()->comment('Reason for adjustments');
            
            // Payment details
            $table->decimal('net_commission', 15, 2)->default(0)->comment('Net commission after deductions');
            $table->enum('payment_status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->date('payment_date')->nullable()->comment('Commission payment date');
            $table->string('payment_method', 50)->nullable()->comment('Payment method');
            $table->string('payment_reference', 100)->nullable()->comment('Payment reference number');
            
            // Approval workflow
            $table->boolean('is_approved')->default(false)->comment('Commission approved');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Additional information
            $table->json('sales_breakdown')->nullable()->comment('Detailed sales breakdown');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional commission data');
            
            $table->foreignId('calculated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'employee_id', 'period_start_date', 'period_end_date'], 'unique_employee_commission_period');
            $table->index(['store_id', 'commission_period']);
            $table->index(['employee_id', 'period_start_date']);
            $table->index(['employee_id', 'payment_status']);
            $table->index(['payment_status']);
            $table->index(['is_approved']);
            $table->index(['approved_by']);
            $table->index(['payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_commissions');
    }
};
