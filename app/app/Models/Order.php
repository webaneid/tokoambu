<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'type',
        'preorder_period_id',
        'status',
        'payment_status',
        'payment_method',
        'source',
        'total_amount',
        'subtotal',
        'shipping_cost',
        'tax',
        'total',
        'paid_amount',
        'refund_amount',
        'refund_method',
        'refund_notes',
        'refunded_at',
        'refund_from_account_id',
        'dp_amount',
        'dp_paid_at',
        'dp_payment_deadline',
        'final_payment_deadline',
        'shipping_courier',
        'shipping_service',
        'shipping_etd',
        'shipping_province_id',
        'shipping_city_id',
        'shipping_district_id',
        'shipping_postal_code',
        'shipping_address',
        'shipping_city',
        'shipping_province',
        'customer_name',
        'customer_email',
        'customer_phone',
        'notes',
        'invoice_sent_at',
        'ip_address',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'refunded_at' => 'datetime',
        'dp_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'invoice_sent_at' => 'datetime',
        'dp_paid_at' => 'datetime',
        'dp_payment_deadline' => 'datetime',
        'final_payment_deadline' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function preorderPeriod()
    {
        return $this->belongsTo(PreorderPeriod::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    public function shippingProvince()
    {
        return $this->belongsTo(Province::class, 'shipping_province_id');
    }

    public function shippingCity()
    {
        return $this->belongsTo(City::class, 'shipping_city_id');
    }

    public function shippingDistrict()
    {
        return $this->belongsTo(District::class, 'shipping_district_id');
    }

    public function refundFromAccount()
    {
        return $this->belongsTo(BankAccount::class, 'refund_from_account_id');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function remainingAmount()
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Check if order is a preorder
     */
    public function isPreorder(): bool
    {
        return $this->type === 'preorder';
    }

    /**
     * Check if DP payment is expired
     */
    public function isDpPaymentExpired(): bool
    {
        if (!$this->isPreorder() || !$this->dp_payment_deadline) {
            return false;
        }

        return now()->isAfter($this->dp_payment_deadline) && !$this->dp_paid_at;
    }

    /**
     * Check if final payment is expired
     */
    public function isFinalPaymentExpired(): bool
    {
        if (!$this->isPreorder() || !$this->final_payment_deadline) {
            return false;
        }

        return now()->isAfter($this->final_payment_deadline) && $this->status === 'waiting_payment';
    }

    /**
     * Get remaining DP amount
     */
    public function remainingDpAmount()
    {
        if (!$this->dp_amount) {
            return 0;
        }

        return max(0, $this->dp_amount - $this->paid_amount);
    }

    /**
     * Get remaining final payment amount
     */
    public function remainingFinalAmount()
    {
        if (!$this->dp_amount) {
            return $this->remainingAmount();
        }

        return max(0, $this->total_amount - $this->dp_amount - ($this->paid_amount - $this->dp_amount));
    }

    /**
     * Available order statuses including preorder statuses
     */
    public static function getStatuses(): array
    {
        return [
            'draft' => 'Draft',
            'waiting_dp' => 'Menunggu DP',
            'dp_paid' => 'DP Lunas',
            'product_ready' => 'Produk Siap',
            'waiting_payment' => 'Menunggu Pembayaran',
            'dp_paid' => 'DP Dibayar',
            'paid' => 'Lunas',
            'packed' => 'Dikemas',
            'shipped' => 'Dikirim',
            'done' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
    }

    /**
     * Get status badge color
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'waiting_dp' => 'yellow',
            'dp_paid' => 'blue',
            'product_ready' => 'purple',
            'waiting_payment' => 'yellow',
            'paid' => 'green',
            'packed' => 'indigo',
            'shipped' => 'blue',
            'done' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}
