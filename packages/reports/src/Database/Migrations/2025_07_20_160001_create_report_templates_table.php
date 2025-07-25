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
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('code', 100)->comment('Report template code');
            $table->string('name', 200)->comment('Report template name');
            $table->text('description')->nullable();
            
            // Report classification
            $table->enum('category', [
                'sales', 'daily_summary', 'inventory', 'customer', 'employee', 
                'financial', 'channel', 'supplier', 'purchasing'
            ])->comment('Report category');
            $table->enum('type', ['summary', 'detail', 'comparison', 'trend', 'custom'])->default('summary');
            $table->enum('frequency', ['real_time', 'daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'custom'])->default('daily');
            
            // Data source and query
            $table->json('data_sources')->comment('Tables/models used for data');
            $table->json('filters')->nullable()->comment('Default filters configuration');
            $table->json('columns')->comment('Report columns configuration');
            $table->json('grouping')->nullable()->comment('Grouping configuration');
            $table->json('sorting')->nullable()->comment('Sorting configuration');
            $table->json('calculations')->nullable()->comment('Calculations and aggregations');
            
            // Visualization
            $table->enum('output_format', ['table', 'chart', 'dashboard', 'export'])->default('table');
            $table->json('chart_config')->nullable()->comment('Chart configuration if applicable');
            $table->json('layout_config')->nullable()->comment('Layout and styling configuration');
            
            // Access control
            $table->json('permissions')->nullable()->comment('Who can access this report');
            $table->boolean('is_public')->default(false)->comment('Public report accessible to all users');
            $table->boolean('is_system')->default(false)->comment('System report (cannot be deleted)');
            
            // Status and settings
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_refresh')->default(false)->comment('Auto refresh data');
            $table->integer('refresh_interval')->default(60)->comment('Refresh interval in minutes');
            $table->boolean('cache_enabled')->default(true)->comment('Enable result caching');
            $table->integer('cache_duration')->default(30)->comment('Cache duration in minutes');
            
            // Additional settings
            $table->json('email_settings')->nullable()->comment('Email delivery settings');
            $table->json('export_settings')->nullable()->comment('Export format settings');
            $table->json('metadata')->nullable()->comment('Additional template data');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'code'], 'unique_store_report_template_code');
            $table->index(['store_id', 'category']);
            $table->index(['store_id', 'type']);
            $table->index(['store_id', 'is_active']);
            $table->index(['category', 'type']);
            $table->index(['is_system']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};
