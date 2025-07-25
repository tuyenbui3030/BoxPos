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
        Schema::create('report_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('template_id')->constrained('report_templates')->onDelete('cascade');
            $table->string('name', 200)->comment('Schedule name');
            $table->text('description')->nullable();
            
            // Schedule configuration
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->comment('Schedule frequency');
            $table->json('schedule_config')->comment('Detailed schedule configuration');
            $table->time('run_time')->default('08:00')->comment('Time to run the report');
            $table->json('run_days')->nullable()->comment('Days to run (for weekly/monthly)');
            $table->integer('day_of_month')->nullable()->comment('Day of month (for monthly)');
            $table->integer('day_of_week')->nullable()->comment('Day of week (for weekly)');
            
            // Report parameters
            $table->json('default_filters')->nullable()->comment('Default filters for scheduled reports');
            $table->json('parameters')->nullable()->comment('Report parameters');
            $table->boolean('auto_period')->default(true)->comment('Auto calculate period based on frequency');
            $table->integer('period_offset')->default(0)->comment('Period offset (e.g., -1 for previous month)');
            
            // Delivery settings
            $table->boolean('auto_email')->default(false)->comment('Auto email when generated');
            $table->json('email_recipients')->nullable()->comment('Email recipients');
            $table->string('email_subject', 200)->nullable();
            $table->text('email_body')->nullable();
            $table->json('email_formats')->nullable()->comment('Email attachment formats');
            
            // Export settings
            $table->boolean('auto_export')->default(false)->comment('Auto export to file');
            $table->json('export_formats')->nullable()->comment('Export formats (pdf, excel, csv)');
            $table->string('export_path', 500)->nullable()->comment('Export file path');
            
            // Status and execution
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->integer('run_count')->default(0)->comment('Total number of runs');
            $table->integer('success_count')->default(0)->comment('Successful runs');
            $table->integer('failure_count')->default(0)->comment('Failed runs');
            $table->text('last_error')->nullable();
            
            // Retention settings
            $table->boolean('auto_cleanup')->default(true)->comment('Auto cleanup old reports');
            $table->integer('retention_days')->default(90)->comment('Days to keep reports');
            $table->integer('max_instances')->default(50)->comment('Maximum instances to keep');
            
            // Additional settings
            $table->json('metadata')->nullable()->comment('Additional schedule data');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index(['store_id', 'template_id']);
            $table->index(['store_id', 'is_active']);
            $table->index(['template_id', 'is_active']);
            $table->index(['is_active', 'next_run_at']);
            $table->index(['frequency']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_schedules');
    }
};
