<?php

namespace Packages\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Packages\Customer\Builders\CustomerBuilder;
use Packages\User\Models\User;
use Packages\Tenant\Traits\HasTenantScope;

class Customer extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'customer_code',
        'customer_name',
        'phone_number',
        'email',
        'address',
        'customer_type',
        'gender',
        'birthday',
        'customer_group',
        'current_debt',
        'total_sales',
        'total_sales_minus_returns',
        'last_transaction_at',
        'created_by',
    ];

    protected $casts = [
        'birthday' => 'date',
        'last_transaction_at' => 'datetime',
        'current_debt' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'total_sales_minus_returns' => 'decimal:2',
    ];

    /**
     * Create a new Eloquent query builder for the model.
     */
    public function newEloquentBuilder($query)
    {
        return new CustomerBuilder($query);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('customer_code', 'like', "%{$search}%")
              ->orWhere('customer_name', 'like', "%{$search}%")
              ->orWhere('phone_number', 'like', "%{$search}%");
        });
    }

    public function scopeWithDebt($query)
    {
        return $query->where('current_debt', '>', 0);
    }

    public function scopeByGroup($query, $group)
    {
        return $query->where('customer_group', $group);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('customer_type', $type);
    }

    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeBirthdayBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('birthday', [$startDate, $endDate]);
    }

    public function scopeLastTransactionBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('last_transaction_at', [$startDate, $endDate]);
    }

    public function scopeSalesBetween($query, $minSales, $maxSales)
    {
        return $query->whereBetween('total_sales', [$minSales, $maxSales]);
    }

    public function getFormattedCurrentDebtAttribute()
    {
        return number_format($this->current_debt, 2);
    }

    public function getFormattedTotalSalesAttribute()
    {
        return number_format($this->total_sales, 2);
    }

    public function getFormattedTotalSalesMinusReturnsAttribute()
    {
        return number_format($this->total_sales_minus_returns, 2);
    }

    public static function generateCustomerCode()
    {
        $lastCustomer = static::orderBy('id', 'desc')->first();
        $number = $lastCustomer ? ($lastCustomer->id + 1) : 1;
        return 'CUS' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Packages\Customer\Database\Factories\CustomerFactory::new();
    }
}
