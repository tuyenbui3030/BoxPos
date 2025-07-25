<?php

namespace Packages\Notifications\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'code',
        'name',
        'description',
        'type',
        'category',
        'trigger',
        'subject',
        'content',
        'content_html',
        'placeholders',
        'delivery_channels',
        'priority',
        'is_active',
        'schedule_config',
        'retry_config',
        'rate_limit',
        'conditions',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'delivery_channels' => 'array',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'schedule_config' => 'array',
        'retry_config' => 'array',
        'rate_limit' => 'array',
        'conditions' => 'array',
        'metadata' => 'array',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
