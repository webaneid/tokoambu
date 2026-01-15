@extends('storefront.layouts.public-invoice')

@section('title', 'Invoice ' . $order->order_number)

@section('content')
<section class="public-invoice__card">
    <header class="public-invoice__header">
        <div class="public-invoice__title">
            <span class="public-invoice__badge">Invoice</span>
            <h1 class="public-invoice__number">{{ $order->order_number }}</h1>
        </div>
        <div class="public-invoice__store">
            <p class="public-invoice__store-name">{{ $store['name'] ?? config('app.name', 'Toko Ambu') }}</p>
            @if(!empty($store['phone']))
                <p>{{ $store['phone'] }}</p>
            @endif
            @if(!empty($store['address']))
                <p>{{ $store['address'] }}</p>
            @endif
            @if(!empty($store['city']))
                <p>{{ $store['city'] }}</p>
            @endif
        </div>
    </header>

    <div class="public-invoice__meta">
        <div class="public-invoice__bill-to">
            <p class="public-invoice__label">Ditagihkan ke</p>
            <p class="public-invoice__value">{{ $order->customer->name }}</p>
            @if($order->customer->address)
                <p>{{ $order->customer->address }}</p>
            @endif
            @if($order->customer->phone)
                <p>{{ $order->customer->phone }}</p>
            @endif
        </div>
        <div class="public-invoice__date">
            <p class="public-invoice__label">Tanggal</p>
            <p class="public-invoice__value">{{ $order->created_at->format('d M Y') }}</p>
        </div>
    </div>

    <div class="public-invoice__table">
        <div class="public-invoice__table-head">
            <span>Produk</span>
            <span class="public-invoice__table-qty">Qty</span>
            <span class="public-invoice__table-price">Harga</span>
            <span class="public-invoice__table-total">Jumlah</span>
        </div>
        <div class="public-invoice__table-body">
            @foreach($order->items as $item)
                <div class="public-invoice__table-row">
                    <span>{{ $item->product->name }}</span>
                    <span class="public-invoice__table-qty">{{ $item->quantity }}</span>
                    <span class="public-invoice__table-price">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                    <span class="public-invoice__table-total">Rp {{ number_format($item->subtotal ?? ($item->quantity * $item->unit_price), 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="public-invoice__summary">
        <div class="public-invoice__summary-row">
            <span>Ongkos Kirim{{ $order->shipping_courier ? ' (' . strtoupper($order->shipping_courier) . ')' : '' }}</span>
            <span>Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="public-invoice__summary-row public-invoice__summary-row--total">
            <span>Total</span>
            <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    @if($order->notes)
        <div class="public-invoice__notes">
            <p class="public-invoice__label">Catatan</p>
            <p>{{ $order->notes }}</p>
        </div>
    @endif

    <div class="public-invoice__payments">
        <h2 class="public-invoice__section-title">Histori Pembayaran</h2>
        @if($order->payments->count())
            <div class="public-invoice__payments-table">
                <div class="public-invoice__payments-head">
                    <span>Tanggal</span>
                    <span>Metode</span>
                    <span>Status</span>
                    <span class="public-invoice__payments-amount">Jumlah</span>
                </div>
                @foreach($order->payments as $payment)
                    <div class="public-invoice__payments-row">
                        <span>{{ $payment->paid_at?->format('d M Y H:i') ?? '-' }}</span>
                        <span>{{ $payment->method ?? '-' }}</span>
                        <span class="public-invoice__status">{{ $payment->status ?? '-' }}</span>
                        <span class="public-invoice__payments-amount">Rp {{ number_format($payment->amount ?? 0, 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="public-invoice__payments-summary">
                    <div>
                        <span>Total Dibayar</span>
                        <strong>Rp {{ number_format($order->paid_amount ?? 0, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span>Sisa Tagihan</span>
                        <strong class="public-invoice__remaining">Rp {{ number_format($order->remainingAmount(), 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        @else
            <p class="public-invoice__empty">Belum ada pembayaran.</p>
        @endif
    </div>

    <div class="public-invoice__actions">
        @if($order->remainingAmount() > 0)
            <a href="{{ $paymentUrl }}" class="btn btn--primary btn--full">Bayar Sekarang</a>
        @endif
        <a href="{{ $downloadUrl }}" class="btn btn--primary btn--full">Download PDF</a>
    </div>
</section>
@endsection
