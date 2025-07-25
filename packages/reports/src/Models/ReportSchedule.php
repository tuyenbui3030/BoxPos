<?php

namespace Packages\Reports\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class ReportSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'template_id',
        'name',
        'description',
        'frequency',
        'schedule_config',
        'run_time',
        'run_days',
        'day_of_month',
        'day_of_week',
        'default_filters',
        'default_parameters',
        'output_formats',
        'delivery_method',
        'email_recipients',
        'webhook_url',
        'is_active',
        'timezone',
        'last_run_at',
        'next_run_at',
        'run_count',
        'success_count',
        'failure_count',
        'last_error',
        'retention_days',
        'max_instances',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'schedule_config' => 'array',
        'run_days' => 'array',
        'default_filters' => 'array',
        'default_parameters' => 'array',
        'output_formats' => 'array',
        'email_recipients' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'run_count' => 'integer',
        'success_count' => 'integer',
        'failure_count' => 'integer',
        'retention_days' => 'integer',
        'max_instances' => 'integer',
        'metadata' => 'array',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
