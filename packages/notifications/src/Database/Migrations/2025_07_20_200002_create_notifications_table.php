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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('notification_templates')->onDelete('set null');
            $table->string('notification_number', 100)->comment('Notification reference number');
            
            // Notification details
            $table->enum('type', ['email', 'sms', 'push', 'in_app', 'webhook'])->comment('Notification type');
            $table->enum('category', [
                'order', 'payment', 'inventory', 'customer', 'employee', 
                'system', 'marketing', 'reminder', 'alert'
            ])->comment('Notification category');
            $table->string('trigger', 100)->comment('Event that triggered notification');
            $table->integer('priority')->default(5)->comment('Priority (1-10)');
            
            // Content
            $table->string('subject', 300)->nullable()->comment('Notification subject/title');
            $table->text('content')->comment('Notification content');
            $table->text('content_html')->nullable()->comment('HTML content');
            $table->json('data')->nullable()->comment('Additional notification data');
            
            // Recipients
            $table->string('recipient_type', 50)->comment('customer, user, admin, custom');
            $table->unsignedBigInteger('recipient_id')->nullable()->comment('Recipient ID (customer_id or user_id)');
            $table->string('recipient_email', 200)->nullable();
            $table->string('recipient_phone', 20)->nullable();
            $table->string('recipient_name', 200)->nullable();
            $table->json('additional_recipients')->nullable()->comment('CC/BCC recipients');
            
            // Related entities
            $table->string('related_type', 100)->nullable()->comment('Related entity type');
            $table->unsignedBigInteger('related_id')->nullable()->comment('Related entity ID');
            $table->json('related_data')->nullable()->comment('Related entity data snapshot');
            
            // Scheduling and timing
            $table->datetime('scheduled_at')->comment('When to send notification');
            $table->datetime('sent_at')->nullable()->comment('When notification was sent');
            $table->datetime('delivered_at')->nullable()->comment('When notification was delivered');
            $table->datetime('read_at')->nullable()->comment('When notification was read');
            $table->datetime('expires_at')->nullable()->comment('When notification expires');
            
            // Status and delivery
            $table->enum('status', ['pending', 'scheduled', 'sending', 'sent', 'delivered', 'failed', 'cancelled', 'expired'])->default('pending');
            $table->text('failure_reason')->nullable()->comment('Reason for failure');
            $table->integer('retry_count')->default(0)->comment('Number of retry attempts');
            $table->datetime('next_retry_at')->nullable()->comment('Next retry time');
            $table->integer('max_retries')->default(3)->comment('Maximum retry attempts');
            
            // Provider details
            $table->string('provider', 100)->nullable()->comment('Service provider used');
            $table->string('provider_message_id', 200)->nullable()->comment('Provider message ID');
            $table->json('provider_response')->nullable()->comment('Provider response data');
            $table->decimal('cost', 10, 4)->default(0)->comment('Cost of sending notification');
            $table->string('currency', 3)->default('VND');
            
            // Tracking and analytics
            $table->boolean('is_read')->default(false)->comment('Notification has been read');
            $table->boolean('is_clicked')->default(false)->comment('Notification has been clicked');
            $table->integer('click_count')->default(0)->comment('Number of clicks');
            $table->datetime('first_clicked_at')->nullable();
            $table->datetime('last_clicked_at')->nullable();
            $table->json('tracking_data')->nullable()->comment('Tracking and analytics data');
            
            // User preferences
            $table->boolean('can_unsubscribe')->default(true)->comment('User can unsubscribe');
            $table->boolean('is_unsubscribed')->default(false)->comment('User has unsubscribed');
            $table->datetime('unsubscribed_at')->nullable();
            
            // Additional information
            $table->json('metadata')->nullable()->comment('Additional notification data');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->unique(['store_id', 'notification_number'], 'unique_store_notification_number');
            $table->index(['store_id', 'template_id']);
            $table->index(['store_id', 'type']);
            $table->index(['store_id', 'category']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'scheduled_at']);
            $table->index(['recipient_type', 'recipient_id']);
            $table->index(['recipient_email']);
            $table->index(['recipient_phone']);
            $table->index(['related_type', 'related_id']);
            $table->index(['status', 'scheduled_at']);
            $table->index(['status', 'next_retry_at']);
            $table->index(['trigger']);
            $table->index(['priority']);
            $table->index(['is_read']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
