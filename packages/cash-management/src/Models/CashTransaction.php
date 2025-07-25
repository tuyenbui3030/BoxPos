<?php

namespace Packages\CashManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\User\Models\User;

class CashTransaction extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'account_id',
        'category_id',
        'transaction_number',
        'transaction_date',
        'type',
        'amount',
        'currency',
        'exchange_rate',
        'base_amount',
        'transfer_to_account_id',
        'transfer_from_account_id',
        'transfer_reference',
        'payment_method',
        'payment_reference',
        'payer_payee',
        'payer_payee_details',
        'related_document_type',
        'related_document_id',
        'related_document_number',
        'status',
        'is_reconciled',
        'reconciled_date',
        'reconciled_by',
        'requires_approval',
        'is_approved',
        'approved_by',
        'approved_at',
        'approval_notes',
        'description',
        'notes',
        'attachments',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'reconciled_date' => 'date',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'base_amount' => 'decimal:2',
        'is_reconciled' => 'boolean',
        'requires_approval' => 'boolean',
        'is_approved' => 'boolean',
        'attachments' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class, 'account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CashCategory::class, 'category_id');
    }

    public function transferToAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class, 'transfer_to_account_id');
    }

    public function transferFromAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class, 'transfer_from_account_id');
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeTransfers($query)
    {
        return $query->whereIn('type', ['transfer_in', 'transfer_out']);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('requires_approval', true)
                    ->where('is_approved', false);
    }

    public function scopeUnreconciled($query)
    {
        return $query->where('is_reconciled', false);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helper methods
    public static function generateTransactionNumber(int $storeId): string
    {
        $date = now()->format('Ymd');
        $lastTransaction = static::where('store_id', $storeId)
            ->where('transaction_number', 'like', "TXN{$date}%")
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastTransaction ? (intval(substr($lastTransaction->transaction_number, -4)) + 1) : 1;
        return "TXN{$date}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function calculateBaseAmount(): void
    {
        $this->base_amount = $this->amount * $this->exchange_rate;
    }

    public function isIncome(): bool
    {
        return $this->type === 'income';
    }

    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }

    public function isTransfer(): bool
    {
        return in_array($this->type, ['transfer_in', 'transfer_out']);
    }

    public function isTransferIn(): bool
    {
        return $this->type === 'transfer_in';
    }

    public function isTransferOut(): bool
    {
        return $this->type === 'transfer_out';
    }

    public function canBeApproved(): bool
    {
        return $this->requires_approval && 
               !$this->is_approved && 
               $this->status === 'pending';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'approved']) && 
               !$this->is_reconciled;
    }

    public function canBeReconciled(): bool
    {
        return $this->status === 'completed' && 
               !$this->is_reconciled;
    }

    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->isExpense() || $this->isTransferOut() ? '-' : '+';
        return $sign . number_format($this->amount, 0, ',', '.') . ' ' . $this->currency;
    }

    public function getTransactionTypeDisplayAttribute(): string
    {
        return match($this->type) {
            'income' => 'Thu',
            'expense' => 'Chi',
            'transfer_in' => 'Chuyển vào',
            'transfer_out' => 'Chuyển ra',
            default => $this->type
        };
    }

    public function hasRelatedDocument(): bool
    {
        return !empty($this->related_document_type) && !empty($this->related_document_id);
    }

    public function getRelatedDocumentDisplayAttribute(): string
    {
        if (!$this->hasRelatedDocument()) {
            return 'N/A';
        }
        
        return $this->related_document_number ?: 
               ($this->related_document_type . ' #' . $this->related_document_id);
    }
}
