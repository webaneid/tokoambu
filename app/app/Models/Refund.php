<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable = [
        'order_id',
        'amount',
        'payment_method',
        'customer_bank_account_id',
        'shop_bank_account_id',
        'payment_media_id',
        'transfer_fee',
        'reason',
        'notes',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'ledger_entry_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transfer_fee' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ledgerEntry(): BelongsTo
    {
        return $this->belongsTo(LedgerEntry::class);
    }

    public function customerBankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'customer_bank_account_id');
    }

    public function shopBankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'shop_bank_account_id');
    }

    public function paymentMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'payment_media_id');
    }
}
