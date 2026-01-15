@extends('storefront.layouts.app-mobile')

@section('content')
<div class="ipaymu-payment">
    <!-- Header -->
    <div class="ipaymu-payment-header">
        <a href="{{ $paymentRoutes['select'] }}" class="ipaymu-payment-header__back">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5z"/>
            </svg>
        </a>
        <h1 class="ipaymu-payment-header__title">Pembayaran iPaymu</h1>
    </div>

    <!-- Payment Summary -->
    <div class="payment-amount-card">
        <div class="payment-amount-card__label">Jumlah yang Harus Dibayar</div>
        <div class="payment-amount-card__amount">Rp {{ number_format($paymentAmount, 0, ',', '.') }}</div>
        @if($isDpPayment)
        <div class="payment-amount-card__note">
            Pembayaran DP ({{ \App\Models\Setting::getPreorderDpPercentage() }}%) untuk pesanan {{ $order->order_number }}
        </div>
        @else
        <div class="payment-amount-card__note">
            Pembayaran untuk pesanan {{ $order->order_number }}
        </div>
        @endif
    </div>

    <!-- iPaymu Info -->
    <div class="ipaymu-info-card">
        <div class="ipaymu-info-card__header">
            <div class="ipaymu-info-card__icon">
                <svg width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"/>
                    <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"/>
                </svg>
            </div>
            <h2 class="ipaymu-info-card__title">Pembayaran iPaymu</h2>
        </div>
        <div class="ipaymu-info-card__body">
            <p class="ipaymu-info-card__text">
                Pilih metode pembayaran yang Anda inginkan:
            </p>
        </div>
    </div>

    <!-- Payment Channel Selection Form -->
    <form action="{{ $paymentRoutes['ipaymuCreate'] }}" method="POST">
        @csrf

        @if(!empty($channels) && is_array($channels))
            @foreach($channels as $method)
                @if(!empty($method['Channels']) && is_array($method['Channels']))
                    <div class="ipaymu-methods-card">
                        <h3 class="ipaymu-methods-card__title">{{ $method['Name'] }}</h3>
                        <div class="ipaymu-channel-list">
                            @foreach($method['Channels'] as $channel)
                                @if(isset($channel['FeatureStatus']) && $channel['FeatureStatus'] === 'active')
                                    <label class="ipaymu-channel-option">
                                        <input
                                            type="radio"
                                            name="payment_channel"
                                            value="{{ $method['Code'] }}:{{ $channel['Code'] }}"
                                            required
                                        >
                                        <div class="ipaymu-channel-option__content">
                                            <div class="ipaymu-channel-option__header">
                                                @if(!empty($channel['Logo']))
                                                    <div class="ipaymu-channel-option__logo">
                                                        <img src="{{ $channel['Logo'] }}" alt="{{ $channel['Name'] }}">
                                                    </div>
                                                @else
                                                    <div class="ipaymu-channel-option__icon">
                                                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"/>
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div class="ipaymu-channel-option__info">
                                                    <span class="ipaymu-channel-option__name">{{ $channel['Name'] }}</span>
                                                    @if(isset($channel['TransactionFee']))
                                                        <small class="ipaymu-channel-option__fee">
                                                            Fee:
                                                            @if($channel['TransactionFee']['ActualFeeType'] === 'FLAT')
                                                                Rp {{ number_format($channel['TransactionFee']['ActualFee'], 0, ',', '.') }}
                                                            @else
                                                                {{ $channel['TransactionFee']['ActualFee'] }}%
                                                            @endif
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="ipaymu-channel-option__radio"></div>
                                        </div>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="ipaymu-methods-card">
                <p style="color: var(--ane-color-text-muted); text-align: center;">Tidak ada metode pembayaran tersedia</p>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="ipaymu-actions">
            <button type="submit" class="btn btn--primary btn--full">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                </svg>
                Lanjutkan Pembayaran
            </button>
            <a href="{{ route('customer.order.show', $order->id) }}" class="btn btn--outline btn--full">
                Kembali ke Detail Pesanan
            </a>
        </div>
    </form>
</div>
@endsection
