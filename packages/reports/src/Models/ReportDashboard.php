<?php

namespace Packages\Reports\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class ReportDashboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'layout_config',
        'widgets',
        'filters',
        'refresh_interval',
        'permissions',
        'is_active',
        'is_default',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'layout_config' => 'array',
        'widgets' => 'array',
        'filters' => 'array',
        'refresh_interval' => 'integer',
        'permissions' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
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
