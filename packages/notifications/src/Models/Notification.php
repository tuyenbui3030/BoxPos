<?php

namespace Packages\Notifications\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'template_id',
        'notification_number',
        'type',
        'category',
        'trigger',
        'priority',
        'subject',
        'content',
        'content_html',
        'data',
        'recipient_type',
        'recipient_id',
        'recipient_email',
        'recipient_phone',
        'recipient_name',
        'additional_recipients',
        'related_type',
        'related_id',
        'status',
        'scheduled_at',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_at',
        'retry_count',
        'error_message',
        'delivery_attempts',
        'action_url',
        'action_text',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'priority' => 'integer',
        'data' => 'array',
        'additional_recipients' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
        'retry_count' => 'integer',
        'delivery_attempts' => 'array',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }
}
