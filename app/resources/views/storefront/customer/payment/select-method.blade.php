@extends('storefront.layouts.app-mobile')

@section('content')
<div class="payment-select">
    <!-- Header -->
    <div class="payment-select-header">
        <a href="{{ route('customer.order.show', $order->id) }}" class="payment-select-header__back">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5z"/>
            </svg>
        </a>
        <h1 class="payment-select-header__title">Pilih Metode Pembayaran</h1>
    </div>

    <!-- Order Summary -->
    <div class="payment-summary-card">
        <div class="payment-summary-card__row">
            <span class="payment-summary-card__label">Nomor Pesanan</span>
            <span class="payment-summary-card__value">{{ $order->order_number }}</span>
        </div>
        @if($isDpPayment)
        <div class="payment-summary-card__row">
            <span class="payment-summary-card__label">Jenis Pembayaran</span>
            <span class="payment-summary-card__value payment-summary-card__value--highlight">
                DP ({{ \App\Models\Setting::getPreorderDpPercentage() }}%)
            </span>
        </div>
        @endif
        <div class="payment-summary-card__row payment-summary-card__row--total">
            <span class="payment-summary-card__label">Jumlah yang Harus Dibayar</span>
            <span class="payment-summary-card__value payment-summary-card__value--amount">
                Rp {{ number_format($paymentAmount, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- Payment Methods List -->
    <div class="payment-methods">
        <h2 class="payment-methods__title">Pilih Metode Pembayaran</h2>

        @forelse($paymentMethods as $key => $method)
        @php
            $routeMap = [
                'bank_transfer' => $paymentRoutes['bankTransfer'],
                'ipaymu' => $paymentRoutes['ipaymu'],
            ];
            $href = $routeMap[$key] ?? '#';
            $isDisabled = !in_array($key, ['bank_transfer', 'ipaymu']);
        @endphp
        <a href="{{ $href }}"
           class="payment-method-card {{ $isDisabled ? 'payment-method-card--disabled' : '' }}"
           @if($isDisabled) onclick="event.preventDefault(); alert('Metode pembayaran ini sedang dalam pengembangan');" @endif>
            <div class="payment-method-card__icon">
                @if($method['icon'] === 'cash')
                <svg width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M1 3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1H1zm7 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/>
                    <path d="M0 5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V5zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V7a2 2 0 0 1-2-2H3z"/>
                </svg>
                @elseif($method['icon'] === 'bank')
                <svg width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                    <path d="m8 0 6.61 3h.89a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v7a.5.5 0 0 1 .485.38l.5 2a.498.498 0 0 1-.485.62H.5a.498.498 0 0 1-.485-.62l.5-2A.501.501 0 0 1 1 13V6H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 3h.89L8 0ZM3.777 3h8.447L8 1 3.777 3ZM2 6v7h1V6H2Zm2 0v7h2.5V6H4Zm3.5 0v7h1V6h-1Zm2 0v7H12V6H9.5ZM13 6v7h1V6h-1Zm2-1V4H1v1h14Zm-.39 9H1.39l-.25 1h13.72l-.25-1Z"/>
                </svg>
                @elseif($method['icon'] === 'wallet')
                <svg width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 3a2 2 0 0 1 2-2h13.5a.5.5 0 0 1 0 1H15v2a1 1 0 0 1 1 1v8.5a1.5 1.5 0 0 1-1.5 1.5h-12A2.5 2.5 0 0 1 0 12.5V3zm1 1.732V12.5A1.5 1.5 0 0 0 2.5 14h12a.5.5 0 0 0 .5-.5V5H2a1.99 1.99 0 0 1-1-.268zM1 3a1 1 0 0 0 1 1h12V2H2a1 1 0 0 0-1 1z"/>
                </svg>
                @elseif($method['icon'] === 'credit-card')
                <svg width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"/>
                    <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"/>
                </svg>
                @endif
            </div>
            <div class="payment-method-card__content">
                <h3 class="payment-method-card__name">{{ $method['name'] }}</h3>
                <p class="payment-method-card__description">{{ $method['description'] }}</p>
            </div>
            <div class="payment-method-card__arrow">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </div>
        </a>
        @empty
        <div class="payment-methods__empty">
            <svg width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
            </svg>
            <p>Tidak ada metode pembayaran yang tersedia</p>
            <p class="payment-methods__empty-hint">Silakan hubungi admin toko</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
