<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    protected $fillable = [
        'purchase_id',
        'amount',
        'status',
        'method',
        'paid_at',
        'supplier_bank_account_id',
        'payer_bank_account_id',
        'notes',
        'ledger_entry_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function ledgerEntry()
    {
        return $this->belongsTo(LedgerEntry::class);
    }

    public function supplierBankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'supplier_bank_account_id');
    }

    public function payerBankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'payer_bank_account_id');
    }

    public function paymentProofs()
    {
        return $this->hasMany(Media::class, 'purchase_payment_id')->where('type', 'payment_proof');
    }
}
