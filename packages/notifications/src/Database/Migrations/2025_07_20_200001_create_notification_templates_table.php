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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('code', 100)->comment('Template code');
            $table->string('name', 200)->comment('Template name');
            $table->text('description')->nullable();
            
            // Template classification
            $table->enum('type', ['email', 'sms', 'push', 'in_app', 'webhook'])->comment('Notification type');
            $table->enum('category', [
                'order', 'payment', 'inventory', 'customer', 'employee', 
                'system', 'marketing', 'reminder', 'alert'
            ])->comment('Notification category');
            $table->enum('trigger', [
                'order_created', 'order_confirmed', 'order_shipped', 'order_delivered', 'order_cancelled',
                'payment_received', 'payment_failed', 'payment_refunded',
                'low_stock', 'out_of_stock', 'stock_received',
                'customer_registered', 'customer_birthday', 'loyalty_points_earned',
                'employee_clock_in', 'employee_clock_out', 'payroll_generated',
                'system_backup', 'system_error', 'login_failed',
                'promotion_started', 'promotion_ending', 'newsletter',
                'appointment_reminder', 'payment_due', 'subscription_expiring',
                'security_alert', 'data_export_ready'
            ])->comment('Event trigger');
            
            // Template content
            $table->string('subject', 300)->nullable()->comment('Email subject or notification title');
            $table->text('content')->comment('Template content with placeholders');
            $table->text('content_html')->nullable()->comment('HTML content for emails');
            $table->json('placeholders')->nullable()->comment('Available placeholders');
            
            // Delivery settings
            $table->json('recipients')->nullable()->comment('Default recipients configuration');
            $table->boolean('send_to_customer')->default(false)->comment('Send to customer');
            $table->boolean('send_to_admin')->default(false)->comment('Send to admin');
            $table->json('admin_roles')->nullable()->comment('Admin roles to notify');
            $table->json('custom_recipients')->nullable()->comment('Custom recipient emails/phones');
            
            // Timing and scheduling
            $table->integer('delay_minutes')->default(0)->comment('Delay before sending');
            $table->json('send_schedule')->nullable()->comment('Schedule when to send');
            $table->boolean('respect_quiet_hours')->default(true)->comment('Respect quiet hours');
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            
            // Channel specific settings
            $table->json('email_settings')->nullable()->comment('Email specific settings');
            $table->json('sms_settings')->nullable()->comment('SMS specific settings');
            $table->json('push_settings')->nullable()->comment('Push notification settings');
            $table->json('webhook_settings')->nullable()->comment('Webhook settings');
            
            // Conditions and filters
            $table->json('conditions')->nullable()->comment('Conditions for sending');
            $table->json('filters')->nullable()->comment('Filters to apply');
            $table->boolean('require_opt_in')->default(false)->comment('Require customer opt-in');
            
            // Status and settings
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false)->comment('System template (cannot be deleted)');
            $table->integer('priority')->default(5)->comment('Priority (1-10)');
            $table->json('metadata')->nullable()->comment('Additional template data');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'code'], 'unique_store_notification_template_code');
            $table->index(['store_id', 'type']);
            $table->index(['store_id', 'category']);
            $table->index(['store_id', 'trigger']);
            $table->index(['store_id', 'is_active']);
            $table->index(['type', 'trigger']);
            $table->index(['is_system']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
