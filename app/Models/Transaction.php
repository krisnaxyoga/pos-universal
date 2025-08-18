<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_number',
        'user_id',
        'customer_id',
        'customer_info',
        'subtotal',
        'discount',
        'tax',
        'total',
        'paid',
        'change',
        'payment_method',
        'status',
        'notes',
        // Draft fields
        'draft_name',
        'is_draft',
        // Refund fields
        'refund_reference_id',
        'refund_amount',
        'refund_reason',
        'refunded_at',
        // iPaymu fields
        'ipaymu_transaction_id',
        'ipaymu_session_id',
        'ipaymu_reference_id',
        'ipaymu_amount',
        'ipaymu_fee',
        'ipaymu_payment_method',
        'ipaymu_payment_channel',
        'ipaymu_payment_code',
        'ipaymu_payment_url',
        'ipaymu_qr_string',
        'ipaymu_expired_date',
        'ipaymu_status',
        'ipaymu_status_code',
        'ipaymu_paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
        'change' => 'decimal:2',
        'customer_info' => 'array',
        // Draft and refund field casts
        'is_draft' => 'boolean',
        'refund_amount' => 'decimal:2',
        'refunded_at' => 'datetime',
        // iPaymu field casts
        'ipaymu_amount' => 'decimal:2',
        'ipaymu_fee' => 'decimal:2',
        'ipaymu_expired_date' => 'datetime',
        'ipaymu_paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function refundReference(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'refund_reference_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Transaction::class, 'refund_reference_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeDraft($query)
    {
        return $query->where('is_draft', true)->where('status', 'draft');
    }

    public function scopeNotDraft($query)
    {
        return $query->where('is_draft', false);
    }

    public function scopeRefunds($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeCanBeRefunded($query)
    {
        return $query->where('status', 'completed')
                    ->where('payment_method', '!=', 'online')
                    ->whereNull('refund_reference_id');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    // Helper methods
    public function isDraft(): bool
    {
        return $this->is_draft && $this->status === 'draft';
    }

    public function canBeRefunded(): bool
    {
        return $this->status === 'completed' && 
               $this->payment_method !== 'online' && 
               is_null($this->refund_reference_id) &&
               $this->refunds()->count() === 0;
    }

    public function isRefund(): bool
    {
        return !is_null($this->refund_reference_id);
    }

    public function getTotalRefundedAmount(): float
    {
        return $this->refunds()->sum('refund_amount') ?? 0;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_number)) {
                $transaction->transaction_number = 'TRX' . date('YmdHis') . rand(100, 999);
            }
        });
    }
}
