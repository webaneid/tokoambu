@extends('storefront.layouts.app-mobile')

@section('content')
<div class="order-detail">
    <!-- Header -->
    <div class="order-detail-header">
        <a href="{{ route('customer.orders') }}" class="order-detail-header__back">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5z"/>
            </svg>
        </a>
        <div class="order-detail-header__info">
            <h1 class="order-detail-header__title">Detail Pesanan</h1>
            <p class="order-detail-header__number">{{ $order->order_number }}</p>
        </div>
        <div class="order-detail-header__type">
            @if($order->type === 'preorder')
                <span class="order-badge order-badge--info">Preorder</span>
            @else
                <span class="order-badge order-badge--success">Order</span>
            @endif
        </div>
    </div>

    <!-- Order Status Timeline -->
    <div class="order-timeline-card">
        <h2 class="order-timeline-card__title">Status Pesanan</h2>
        <div class="order-timeline">
            @if($order->status === 'cancelled')
                <!-- Cancelled State -->
                <div class="timeline-step timeline-step--completed">
                    <div class="timeline-step__marker">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                        </svg>
                    </div>
                    <div class="timeline-step__content">
                        <h3 class="timeline-step__title">Pesanan Dibatalkan</h3>
                        <p class="timeline-step__time">{{ $order->updated_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            @else
                <!-- Draft -->
                <div class="timeline-step timeline-step--completed">
                    <div class="timeline-step__marker">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                        </svg>
                    </div>
                    <div class="timeline-step__content">
                        <h3 class="timeline-step__title">Pesanan Dibuat</h3>
                        <p class="timeline-step__time">{{ $order->created_at->format('d M Y H:i') }}</p>
                    </div>
                </div>

                <!-- Waiting Payment / DP Paid / Paid -->
                <div class="timeline-step {{ in_array($order->status, ['waiting_payment', 'dp_paid', 'paid', 'packed', 'shipped', 'done']) ? 'timeline-step--completed' : '' }}">
                    <div class="timeline-step__marker">
                        @if(in_array($order->status, ['waiting_payment', 'dp_paid', 'paid', 'packed', 'shipped', 'done']))
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                        </svg>
                        @endif
                    </div>
                    <div class="timeline-step__content">
                        <h3 class="timeline-step__title">Pembayaran</h3>
                        <p class="timeline-step__time">
                            @if($order->status === 'waiting_payment')
                                Menunggu Pembayaran
                            @elseif($order->status === 'dp_paid')
                                Bayar DP
                            @elseif(in_array($order->status, ['paid', 'packed', 'shipped', 'done']))
                                Lunas
                            @else
                                Belum dibayar
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Packed -->
                <div class="timeline-step {{ in_array($order->status, ['packed', 'shipped', 'done']) ? 'timeline-step--completed' : '' }}">
                    <div class="timeline-step__marker">
                        @if(in_array($order->status, ['packed', 'shipped', 'done']))
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                        </svg>
                        @endif
                    </div>
                    <div class="timeline-step__content">
                        <h3 class="timeline-step__title">Dikemas</h3>
                        <p class="timeline-step__time">Pesanan sedang dikemas</p>
                    </div>
                </div>

                <!-- Shipped -->
                <div class="timeline-step {{ in_array($order->status, ['shipped', 'done']) ? 'timeline-step--completed' : '' }}">
                    <div class="timeline-step__marker">
                        @if(in_array($order->status, ['shipped', 'done']))
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                        </svg>
                        @endif
                    </div>
                    <div class="timeline-step__content">
                        <h3 class="timeline-step__title">Dikirim</h3>
                        <p class="timeline-step__time">Pesanan dalam perjalanan</p>
                    </div>
                </div>

                <!-- Done -->
                <div class="timeline-step {{ $order->status === 'done' ? 'timeline-step--completed' : '' }}">
                    <div class="timeline-step__marker">
                        @if($order->status === 'done')
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                        </svg>
                        @endif
                    </div>
                    <div class="timeline-step__content">
                        <h3 class="timeline-step__title">Selesai</h3>
                        <p class="timeline-step__time">Pesanan telah selesai</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Order Summary -->
    <div class="order-summary-card">
        <h2 class="order-summary-card__title">Ringkasan Pesanan</h2>
        <div class="order-summary">
            <div class="order-summary__row">
                <span class="order-summary__label">Status Pesanan</span>
                <span class="order-badge order-badge--{{ 
                    $order->status === 'pending' ? 'warning' : 
                    ($order->status === 'completed' ? 'success' : 
                    ($order->status === 'cancelled' ? 'danger' : 'info')) 
                }}">
                    @if($order->status === 'pending')
                        Menunggu
                    @elseif($order->status === 'processing')
                        Diproses
                    @elseif($order->status === 'packed')
                        Dikemas
                    @elseif($order->status === 'shipped')
                        Dikirim
                    @elseif($order->status === 'delivered')
                        Terkirim
                    @elseif($order->status === 'completed')
                        Selesai
                    @elseif($order->status === 'cancelled')
                        Dibatalkan
                    @else
                        {{ ucfirst($order->status) }}
                    @endif
                </span>
            </div>
            <div class="order-summary__row">
                <span class="order-summary__label">Metode Pembayaran</span>
                <span class="order-summary__value">
                    @php
                        $payment = $order->payments()->first();
                        $method = $payment?->method ?? null;
                    @endphp
                    @if($method === 'transfer')
                        Transfer Bank
                    @elseif($method === 'cash')
                        Cash / Tunai
                    @elseif($method === 'cod')
                        COD (Bayar di Tempat)
                    @elseif($method === 'ewallet')
                        E-Wallet
                    @elseif($method)
                        {{ ucfirst(str_replace('_', ' ', $method)) }}
                    @else
                        Belum ada pembayaran
                    @endif
                </span>
            </div>
            <div class="order-summary__row">
                <span class="order-summary__label">Tanggal Pesanan</span>
                <span class="order-summary__value">{{ $order->created_at->format('d M Y, H:i') }}</span>
            </div>
        </div>
    </div>

    @php
        $payments = $order->payments ?? collect();
        $verifiedPayments = $payments->where('status', 'verified');
        $paidTotal = $verifiedPayments->sum('amount');
        $remaining = max(0, ($order->total_amount ?? 0) - $paidTotal);
    @endphp

    <!-- Payment History -->
    <div class="order-payments-card">
        <h2 class="order-payments-card__title">Histori Pembayaran</h2>
        @if($payments->count() > 0)
            <div class="order-payments-list">
                @foreach($payments as $payment)
                    <div class="order-payments-row">
                        <div class="order-payments-row__info">
                            <span class="order-payments-row__date">{{ optional($payment->created_at)->format('d M Y, H:i') }}</span>
                            <div class="order-payments-row__meta">
                                <span class="order-payments-row__method">{{ ucfirst($payment->method ?? 'transfer') }}</span>
                                @php
                                    $statusLabel = match($payment->status) {
                                        'verified' => 'Terverifikasi',
                                        'pending' => 'Menunggu Verifikasi',
                                        'rejected' => 'Ditolak',
                                        default => ucfirst($payment->status ?? 'pending')
                                    };
                                    $statusClass = match($payment->status) {
                                        'verified' => 'order-payments-row__status--success',
                                        'rejected' => 'order-payments-row__status--danger',
                                        default => 'order-payments-row__status--warning'
                                    };
                                @endphp
                                <span class="order-payments-row__status {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                        </div>
                        <span class="order-payments-row__amount">Rp {{ number_format($payment->amount ?? 0, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
            <div class="order-payments-summary">
                <div class="order-payments-summary__row">
                    <span>Sudah Dibayar</span>
                    <strong>Rp {{ number_format($paidTotal, 0, ',', '.') }}</strong>
                </div>
                <div class="order-payments-summary__row">
                    <span>Sisa Tagihan</span>
                    <strong>Rp {{ number_format($remaining, 0, ',', '.') }}</strong>
                </div>
            </div>
        @else
            <p class="order-payments-empty">Belum ada pembayaran.</p>
            <div class="order-payments-summary">
                <div class="order-payments-summary__row">
                    <span>Sudah Dibayar</span>
                    <strong>Rp 0</strong>
                </div>
                <div class="order-payments-summary__row">
                    <span>Sisa Tagihan</span>
                    <strong>Rp {{ number_format($order->total_amount ?? 0, 0, ',', '.') }}</strong>
                </div>
            </div>
        @endif
    </div>

    <!-- Order Items -->
    <div class="order-items-card">
        <h2 class="order-items-card__title">Item Pesanan</h2>
        <div class="order-items-list">
            @foreach($order->items as $item)
            @php
                $originalPrice = $item->price ?? null;
            @endphp
            <div class="order-product-item">
                <div class="order-product-item__info">
                    <h3 class="order-product-item__name">{{ $item->product_name ?? $item->product->name }}</h3>
                    @if($item->product_variant_id)
                        <p class="order-product-item__variant">{{ $item->productVariant?->display_name ?? 'Varian' }}</p>
                    @endif
                    @if($originalPrice !== null && $originalPrice > $item->unit_price)
                        <span class="order-product-item__badge order-product-item__badge--flash">Flash Sale</span>
                    @endif
                </div>
                <div class="order-product-item__details">
                    <div class="order-product-item__row">
                        <span class="order-product-item__label">Harga</span>
                        <div class="order-product-item__price">
                            @if($originalPrice !== null && $originalPrice > $item->unit_price)
                                <span class="order-product-item__price-original">Rp {{ number_format($originalPrice, 0, ',', '.') }}</span>
                            @endif
                            <span class="order-product-item__value">Rp {{ number_format($item->unit_price ?? $item->price, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="order-product-item__row">
                        <span class="order-product-item__label">Jumlah</span>
                        <span class="order-product-item__value">{{ $item->quantity }} pcs</span>
                    </div>
                    <div class="order-product-item__row order-product-item__row--total">
                        <span class="order-product-item__label">Subtotal</span>
                        <span class="order-product-item__value">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Shipping Information -->
    <div class="shipping-info-card">
        <h2 class="shipping-info-card__title">Informasi Pengiriman</h2>
        <div class="shipping-info">
            <!-- Courier Info -->
            @if($order->shipping_courier)
            <div class="shipping-info__section">
                <div class="shipping-info__row">
                    <span class="shipping-info__label">Kurir</span>
                    <span class="shipping-info__value">{{ strtoupper($order->shipping_courier) }}</span>
                </div>
                @if($order->shipping_service)
                <div class="shipping-info__row">
                    <span class="shipping-info__label">Layanan</span>
                    <span class="shipping-info__value">{{ $order->shipping_service }}</span>
                </div>
                @endif
                @if($order->shipping_etd)
                <div class="shipping-info__row">
                    <span class="shipping-info__label">Estimasi</span>
                    <span class="shipping-info__value">{{ $order->shipping_etd }} hari</span>
                </div>
                @endif
                <div class="shipping-info__row">
                    <span class="shipping-info__label">Ongkir</span>
                    <span class="shipping-info__value">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                </div>
            </div>
            @endif

            <!-- Recipient Info -->
            <div class="shipping-info__section">
                <h3 class="shipping-info__subtitle">Penerima</h3>
                <p class="shipping-info__text shipping-info__text--name">{{ $order->customer_name }}</p>
                <p class="shipping-info__text">
                    {{ $order->shipping_address }}, {{ $order->shippingDistrict?->name ?? '' }}{{ $order->shippingDistrict ? ', ' : '' }}{{ $order->shippingCity?->name ?? '' }}{{ $order->shippingCity ? ', ' : '' }}{{ $order->shippingProvince?->name ?? '' }}{{ $order->shipping_postal_code ? ' ' . $order->shipping_postal_code : '' }}
                </p>
            </div>
        </div>
    </div>

    @php
        // Determine if DP is required for preorder
        $dpRequired = \App\Models\Setting::isPreorderDpRequired();
        $showDpButton = $order->isPreorder() && $dpRequired && in_array($order->status, ['waiting_dp', 'draft']);
        $showPaymentButton = false;

        // Show payment button for regular orders
        if (!$order->isPreorder() && in_array($order->status, ['waiting_payment', 'draft'])) {
            $showPaymentButton = true;
        }

        // Show payment button for preorders when product is ready
        if ($order->isPreorder() && in_array($order->status, ['product_ready', 'waiting_payment'])) {
            $showPaymentButton = true;
        }

        // Show payment button if DP is paid but not fully paid
        if ($order->isPreorder() && $order->status === 'dp_paid') {
            $showPaymentButton = false; // Wait until product_ready
        }

        $subtotal = $order->subtotal ?? $order->items->sum('subtotal');
    @endphp

    <!-- Action Buttons -->
    <div class="order-actions">
        <a href="https://wa.me/{{ $storeWhatsapp ?? '6281234567890' }}" class="btn btn--outline btn--full" target="_blank">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.309-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.348.1-.116.133-.217.199-.364.065-.15.033-.283-.033-.386-.065-.1-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
            </svg>
            Hubungi Penjual
        </a>

        <button type="button" class="btn btn--outline btn--full" onclick="window.print()">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 1a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v1h1.5a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H15v1.5a1 1 0 0 1-1 1H3.5a1 1 0 0 1-1-1V11H1.5a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1H2.5V1zm0 5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5H2.5z"/>
            </svg>
            Cetak Invoice
        </button>
    </div>

    @if($showDpButton || $showPaymentButton)
    <!-- Fixed Bottom Payment Summary -->
    <div class="order-bottom-summary">
        <div class="order-bottom-summary__content">
            <div class="order-bottom-summary__details">
                <div class="order-bottom-summary__row">
                    <span class="order-bottom-summary__label">Subtotal</span>
                    <span class="order-bottom-summary__value">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="order-bottom-summary__row">
                    <span class="order-bottom-summary__label">Ongkir</span>
                    <span class="order-bottom-summary__value">Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="order-bottom-summary__row order-bottom-summary__row--total">
                    <span class="order-bottom-summary__label">Total</span>
                    <span class="order-bottom-summary__value">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="order-bottom-summary__action">
                @if($showDpButton)
                <a href="{{ route('customer.payment.select', $order->id) }}" class="btn btn--primary btn--full">
                    Bayar DP ({{ \App\Models\Setting::getPreorderDpPercentage() }}%)
                </a>
                @elseif($showPaymentButton)
                <a href="{{ route('customer.payment.select', $order->id) }}" class="btn btn--primary btn--full">
                    Bayar Sekarang
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
