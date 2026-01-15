<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'shipment_number',
        'recipient_name',
        'recipient_address',
        'courier',
        'tracking_number',
        'tracking_media_id',
        'tracking_payload',
        'tracking_status',
        'tracked_at',
        'delivered_at',
        'received_by',
        'shipping_cost',
        'status',
        'notes',
        'shipped_at',
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
        'shipped_at' => 'datetime',
        'tracked_at' => 'datetime',
        'delivered_at' => 'datetime',
        'tracking_payload' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    protected static function booted()
    {
        static::created(function (self $shipment) {
            if ($shipment->shipment_number) {
                return;
            }

            $shipment->shipment_number = 'SHP-' . $shipment->created_at->format('Ymd') . '-' . $shipment->id;
            $shipment->saveQuietly();
        });
    }

    public function trackingMedia()
    {
        return $this->belongsTo(Media::class, 'tracking_media_id');
    }
}
