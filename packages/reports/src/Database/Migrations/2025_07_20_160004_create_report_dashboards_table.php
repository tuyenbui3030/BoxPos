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
        Schema::create('report_dashboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('code', 100)->comment('Dashboard code');
            $table->string('name', 200)->comment('Dashboard name');
            $table->text('description')->nullable();
            
            // Dashboard configuration
            $table->enum('type', ['executive', 'operational', 'analytical', 'custom'])->default('operational');
            $table->json('layout_config')->comment('Dashboard layout configuration');
            $table->json('widgets')->comment('Dashboard widgets configuration');
            $table->json('filters')->nullable()->comment('Global dashboard filters');
            
            // Access control
            $table->json('permissions')->nullable()->comment('Who can access this dashboard');
            $table->boolean('is_public')->default(false);
            $table->boolean('is_default')->default(false)->comment('Default dashboard for role');
            $table->string('target_role', 50)->nullable()->comment('Target user role');
            
            // Refresh settings
            $table->boolean('auto_refresh')->default(true);
            $table->integer('refresh_interval')->default(300)->comment('Refresh interval in seconds');
            $table->timestamp('last_refreshed_at')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable()->comment('Additional dashboard data');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'code'], 'unique_store_dashboard_code');
            $table->index(['store_id', 'type']);
            $table->index(['store_id', 'is_active']);
            $table->index(['is_default', 'target_role']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_dashboards');
    }
};
