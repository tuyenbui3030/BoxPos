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
        Schema::create('report_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('template_id')->constrained('report_templates')->onDelete('cascade');
            $table->string('instance_number', 50)->comment('Report instance number');
            $table->string('title', 300)->comment('Report instance title');
            
            // Report parameters
            $table->date('report_date')->comment('Report date');
            $table->date('period_start')->comment('Report period start');
            $table->date('period_end')->comment('Report period end');
            $table->json('filters_applied')->nullable()->comment('Filters applied to this instance');
            $table->json('parameters')->nullable()->comment('Report parameters');
            
            // Generation details
            $table->enum('status', ['pending', 'generating', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('generation_time')->nullable()->comment('Generation time in seconds');
            $table->text('error_message')->nullable();
            
            // Data and results
            $table->json('summary_data')->nullable()->comment('Summary statistics');
            $table->longText('report_data')->nullable()->comment('Full report data (JSON)');
            $table->integer('total_records')->default(0)->comment('Total records in report');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('Total amount if applicable');
            
            // File exports
            $table->json('export_files')->nullable()->comment('Generated export files');
            $table->boolean('has_pdf')->default(false);
            $table->boolean('has_excel')->default(false);
            $table->boolean('has_csv')->default(false);
            
            // Access and sharing
            $table->boolean('is_shared')->default(false);
            $table->json('shared_with')->nullable()->comment('Users/roles shared with');
            $table->string('share_token', 100)->nullable()->comment('Public share token');
            $table->timestamp('expires_at')->nullable()->comment('Report expiration');
            
            // Email delivery
            $table->boolean('email_sent')->default(false);
            $table->json('email_recipients')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional instance data');
            
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'instance_number'], 'unique_store_report_instance_number');
            $table->index(['store_id', 'template_id']);
            $table->index(['store_id', 'report_date']);
            $table->index(['store_id', 'status']);
            $table->index(['template_id', 'report_date']);
            $table->index(['status']);
            $table->index(['generated_by']);
            $table->index(['share_token']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_instances');
    }
};
