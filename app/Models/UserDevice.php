<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_name',
        'device_type',
        'browser',
        'platform',
        'ip_address',
        'user_agent',
        'remember_token',
        'last_activity',
        'last_login_at',
        'is_trusted',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'last_login_at' => 'datetime',
        'is_trusted' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDeviceInfoAttribute(): string
    {
        $parts = [];
        
        if ($this->platform) {
            $parts[] = $this->platform;
        }
        
        if ($this->browser) {
            $parts[] = $this->browser;
        }
        
        return implode(' • ', $parts) ?: 'Unknown Device';
    }

    public function getIsCurrentDeviceAttribute(): bool
    {
        return $this->ip_address === request()->ip() && 
               $this->user_agent === request()->userAgent();
    }

    public function scopeActive($query)
    {
        return $query->where('last_activity', '>=', now()->subDays(30));
    }

    public function scopeTrusted($query)
    {
        return $query->where('is_trusted', true);
    }
}
