@props(['shipment' => null, 'order' => null])

@php
    $order = $shipment?->order ?? $order;
    $customer = $order?->customer;
    $items = $order?->items?->values() ?? collect();
    $totalWeight = $items->reduce(function ($carry, $item) {
        $weight = $item->product?->weight_grams ?? 0;
        return $carry + ($weight * $item->quantity);
    }, 0);
    $storeName = \App\Models\Setting::get('store_name', config('app.name', 'Toko'));
    $storePhone = \App\Models\Setting::get('store_phone', '');
    $storeAddress = \App\Models\Setting::get('store_address', '');
    $storeWebsite = \App\Models\Setting::get('store_website', '');
    $courier = $shipment?->courier ?? $order?->shipping_courier;
    $service = $order?->shipping_service;
    $trackingNumber = $shipment?->tracking_number ?? null;
    $shippingCost = $shipment?->shipping_cost ?? $order?->shipping_cost ?? 0;
    $recipientName = $shipment?->recipient_name ?? $customer?->name;
    $baseRecipientAddress = $shipment?->recipient_address
        ?? $order?->shipping_address
        ?? $customer?->full_address
        ?? $customer?->address;
    $districtName = $order?->shippingDistrict?->name ?? $customer?->district?->name;
    $cityName = $order?->shippingCity?->name ?? $customer?->city?->name;
    $provinceName = $order?->shippingProvince?->name ?? $customer?->province?->name;
    $postalCode = $order?->shipping_postal_code ?? $customer?->postal_code;
    $recipientAddressParts = array_filter([
        $baseRecipientAddress,
        $districtName,
        $cityName,
        $provinceName,
        $postalCode,
    ]);
    $recipientAddress = $recipientAddressParts ? implode(', ', $recipientAddressParts) : '-';
    $recipientPhone = $customer?->whatsapp_number ?? $customer?->phone ?? '';
@endphp

<div class="label-header">
    <div class="label-brand">{{ $storeName }}</div>
</div>

    <div class="label-order-info">
        <div class="label-order-number">{{ $order?->order_number ?? '-' }}</div>
        <div class="label-courier-info">
            <div class="label-courier-name">
                {{ $courier ? strtoupper($courier) : '-' }}
                @if (!empty($service))
                    <span style="font-weight: normal; font-size: 12px;">{{ $service }}</span>
                @endif
            </div>
        </div>
        @if (!empty($trackingNumber))
            <div class="label-tracking">{{ $trackingNumber }}</div>
            <div class="label-muted" style="margin-top: 4px; text-transform: none;">Kode Booking Ini Bukan No Resi Pengiriman</div>
        @endif
    </div>

    <div class="label-meta">
        <div class="label-meta-item">
            <div class="label-muted">Berat</div>
            <div class="label-strong">{{ $totalWeight > 0 ? number_format($totalWeight / 1000, 1, ',', '.') . ' Kg' : '0.2 Kg' }}</div>
        </div>
        <div class="label-meta-item">
            <div class="label-muted">Ongkir</div>
            <div class="label-strong">Rp {{ number_format($shippingCost, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="label-grid">
        <div class="label-block">
            <div class="label-muted">Kepada</div>
            <div class="label-strong">{{ $recipientName ?? '-' }}</div>
            <div class="label-text">{{ $recipientAddress ?? '-' }}</div>
            <div class="label-text">{{ $recipientPhone }}</div>
        </div>
        <div class="label-block">
            <div class="label-muted">Dari</div>
            <div class="label-strong">{{ $storeName }}</div>
            <div class="label-text">{{ $storeAddress }}</div>
            <div class="label-text">{{ $storePhone }}</div>
            @if ($storeWebsite !== '')
                <div class="label-text">{{ $storeWebsite }}</div>
            @endif
        </div>
    </div>

    <div class="label-section">
        <div class="label-muted">Produk</div>
        <table class="label-table">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>SKU</th>
                    <th class="label-right">Qty</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $item->product?->name ?? 'Produk' }}</td>
                        <td>{{ $item->product?->sku ?? '-' }}</td>
                        <td class="label-right">
                            {{ $item->quantity !== null ? number_format($item->quantity, 0, ',', '.') . ' pcs' : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="label-muted">Tidak ada item</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
