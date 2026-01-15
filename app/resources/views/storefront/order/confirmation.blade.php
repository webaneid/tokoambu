@extends('storefront.layouts.app-mobile')

@section('title', 'Pesanan Berhasil - Toko Ambu')

@section('content')
<div class="storefront-app">
    <main class="storefront-main">

        {{-- ========================================== --}}
        {{-- SUCCESS SECTION --}}
        {{-- ========================================== --}}
        <div class="order-confirmation-success">
            <div class="success-icon">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <h1 class="success-title">Pesanan Berhasil Dibuat!</h1>
            <p class="success-subtitle">Terima kasih telah berbelanja di Toko Ambu</p>
        </div>

        {{-- ========================================== --}}
        {{-- ORDER INFO CARD --}}
        {{-- ========================================== --}}
        <div class="order-info-card">
            <div class="info-row">
                <div class="info-item">
                    <span class="info-label">Nomor Pesanan</span>
                    <span class="info-value">{{ $order->order_number }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tanggal Pesanan</span>
                    <span class="info-value">{{ $order->created_at->format('d M Y H:i') }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <span class="info-label">Status Pesanan</span>
                    <span class="status-badge status-{{ $order->status }}">
                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- RECIPIENT INFO --}}
        {{-- ========================================== --}}
        <div class="section-card">
            <div class="section-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <h2 class="section-title">Data Penerima</h2>
            </div>
            <div class="section-content">
                <div class="data-group">
                    <span class="data-label">Nama</span>
                    <span class="data-value">{{ $order->customer->name ?? 'N/A' }}</span>
                </div>
                <div class="data-group">
                    <span class="data-label">Email</span>
                    <span class="data-value">{{ $order->customer->email ?? 'N/A' }}</span>
                </div>
                <div class="data-group">
                    <span class="data-label">Nomor Telepon</span>
                    <span class="data-value">{{ $order->customer->phone ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- SHIPPING ADDRESS --}}
        {{-- ========================================== --}}
        <div class="section-card">
            <div class="section-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <h2 class="section-title">Alamat Pengiriman</h2>
            </div>
            <div class="section-content">
                <p class="address-main">{{ $order->shipping_address }}</p>
                <p class="address-sub">
                    {{ optional($order->shippingDistrict)->name }}, {{ optional($order->shippingCity)->name }},
                    {{ optional($order->shippingProvince)->name }}
                    @if ($order->shipping_postal_code)
                        {{ $order->shipping_postal_code }}
                    @endif
                </p>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- ORDER ITEMS --}}
        {{-- ========================================== --}}
        <div class="section-card">
            <div class="section-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <h2 class="section-title">Detail Pesanan</h2>
            </div>
            <div class="section-content">
                <div class="order-items-list">
                    @foreach ($items as $item)
                        <div class="order-item">
                            <div class="item-info">
                                <h3 class="item-name">{{ $item->product->name }}</h3>
                                @if($item->productVariant)
                                    <p class="item-variant">{{ $item->productVariant->display_name }}</p>
                                @endif
                                <p class="item-sku">SKU: {{ $item->productVariant ? $item->productVariant->sku : $item->product->sku }}</p>
                            </div>
                            <div class="item-quantity">
                                <span>{{ $item->quantity }}x</span>
                            </div>
                            <div class="item-pricing">
                                <span class="item-price">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                <span class="item-subtotal">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- ORDER SUMMARY --}}
        {{-- ========================================== --}}
        <div class="order-summary-card">
            <div class="summary-row">
                <span class="summary-label">Subtotal</span>
                <span class="summary-value">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Ongkir
                    @if ($order->shipping_courier)
                        <span class="shipping-info">({{ $order->shipping_courier }} - {{ $order->shipping_service }})</span>
                    @endif
                </span>
                <span class="summary-value">Rp {{ number_format($shipping, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row summary-total">
                <span class="summary-label">Total</span>
                <span class="summary-value-total">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
            @if($isPreorder && $dpRequired)
                <div class="summary-row summary-dp">
                    <span class="summary-label">DP yang harus dibayar ({{ $dpPercentage }}%)</span>
                    <span class="summary-value-dp">Rp {{ number_format($dpAmount, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        {{-- ========================================== --}}
        {{-- NEXT STEPS --}}
        {{-- ========================================== --}}
        <div class="section-card">
            <div class="section-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                </svg>
                <h2 class="section-title">Langkah Selanjutnya</h2>
            </div>
            <div class="section-content">
                <div class="steps-list">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h3 class="step-title">Konfirmasi Pesanan</h3>
                            <p class="step-desc">Kami akan menghubungi Anda untuk konfirmasi pesanan</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h3 class="step-title">{{ $isPreorder && $dpRequired ? 'Lakukan Pembayaran DP' : 'Lakukan Pembayaran' }}</h3>
                            <p class="step-desc">
                                @if($isPreorder && $dpRequired)
                                    Bayar DP sebesar {{ $dpPercentage }}% (Rp {{ number_format($dpAmount, 0, ',', '.') }})
                                @else
                                    Selesaikan pembayaran sesuai instruksi yang diberikan
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h3 class="step-title">{{ $isPreorder ? 'Produk Diproses' : 'Pesanan Diproses' }}</h3>
                            <p class="step-desc">
                                @if($isPreorder)
                                    Produk akan diproses sesuai periode preorder
                                @else
                                    Setelah pembayaran diterima, pesanan akan segera dikemas
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h3 class="step-title">Pengiriman</h3>
                            <p class="step-desc">
                                @if($isPreorder)
                                    Setelah produk ready dan pelunasan selesai, barang akan dikirim
                                @else
                                    Barang dikirim ke alamat yang Anda berikan
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- ACTION BUTTONS --}}
        {{-- ========================================== --}}
        <div class="action-buttons">
            <a href="{{ route('shop.index') }}" class="btn btn-secondary btn-block">
                Lanjut Belanja
            </a>
            <a href="{{ route('customer.payment.select', $order->id) }}" class="btn btn-primary btn-block">
                {{ $isPreorder && $dpRequired ? 'Bayar DP Sekarang' : 'Bayar Sekarang' }}
            </a>
        </div>

    </main>
</div>
@endsection
