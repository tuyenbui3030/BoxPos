<?php

namespace Packages\CashManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\User\Models\User;

class CashAccount extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'account_code',
        'account_name',
        'description',
        'account_type',
        'currency',
        'bank_name',
        'account_number',
        'account_holder',
        'branch',
        'swift_code',
        'opening_balance',
        'current_balance',
        'opening_date',
        'credit_limit',
        'daily_limit',
        'monthly_limit',
        'require_approval',
        'approval_threshold',
        'is_active',
        'is_default',
        'allow_negative',
        'is_reconciled',
        'last_reconciled_date',
        'notes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'opening_date' => 'date',
        'last_reconciled_date' => 'date',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'daily_limit' => 'decimal:2',
        'monthly_limit' => 'decimal:2',
        'approval_threshold' => 'decimal:2',
        'require_approval' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'allow_negative' => 'boolean',
        'is_reconciled' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class, 'account_id');
    }

    public function transfersIn(): HasMany
    {
        return $this->hasMany(CashTransaction::class, 'transfer_to_account_id');
    }

    public function transfersOut(): HasMany
    {
        return $this->hasMany(CashTransaction::class, 'transfer_from_account_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('account_type', $type);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByCurrency($query, string $currency)
    {
        return $query->where('currency', $currency);
    }

    public function scopeRequiringReconciliation($query)
    {
        return $query->where('is_reconciled', false);
    }

    // Helper methods
    public static function generateAccountCode(int $storeId, string $type): string
    {
        $prefix = match($type) {
            'cash' => 'CASH',
            'bank' => 'BANK',
            'e_wallet' => 'EWLT',
            'credit_card' => 'CARD',
            'petty_cash' => 'PTTY',
            default => 'ACCT'
        };

        $lastAccount = static::where('store_id', $storeId)
            ->where('account_code', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastAccount ? (intval(substr($lastAccount->account_code, -4)) + 1) : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function updateBalance(float $amount, string $type = 'add'): void
    {
        if ($type === 'add') {
            $this->current_balance += $amount;
        } else {
            $this->current_balance -= $amount;
        }
        
        $this->save();
    }

    public function getAvailableBalanceAttribute(): float
    {
        if ($this->account_type === 'credit_card') {
            return $this->credit_limit + $this->current_balance; // Credit cards have negative balance
        }
        
        return $this->current_balance;
    }

    public function isOverLimit(): bool
    {
        if ($this->account_type === 'credit_card') {
            return abs($this->current_balance) > $this->credit_limit;
        }
        
        return !$this->allow_negative && $this->current_balance < 0;
    }

    public function canWithdraw(float $amount): bool
    {
        if ($this->account_type === 'credit_card') {
            return abs($this->current_balance - $amount) <= $this->credit_limit;
        }
        
        if ($this->allow_negative) {
            return true;
        }
        
        return $this->current_balance >= $amount;
    }

    public function requiresApproval(float $amount): bool
    {
        return $this->require_approval && $amount >= $this->approval_threshold;
    }

    public function isCashAccount(): bool
    {
        return $this->account_type === 'cash';
    }

    public function isBankAccount(): bool
    {
        return $this->account_type === 'bank';
    }

    public function isEWallet(): bool
    {
        return $this->account_type === 'e_wallet';
    }

    public function isCreditCard(): bool
    {
        return $this->account_type === 'credit_card';
    }

    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->current_balance, 0, ',', '.') . ' ' . $this->currency;
    }

    public function getAccountDisplayNameAttribute(): string
    {
        if ($this->isBankAccount() && $this->bank_name) {
            return $this->account_name . ' (' . $this->bank_name . ')';
        }
        
        return $this->account_name;
    }

    public function needsReconciliation(): bool
    {
        return !$this->is_reconciled || 
               ($this->last_reconciled_date && $this->last_reconciled_date->diffInDays(now()) > 30);
    }
}
