@extends('storefront.layouts.app-mobile')

@section('content')
<div class="ipaymu-result">
    <!-- Header -->
    <div class="ipaymu-result-header">
        <a href="{{ route('customer.order.show', $order->id) }}" class="ipaymu-result-header__back">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5z"/>
            </svg>
        </a>
        <h1 class="ipaymu-result-header__title">Detail Pembayaran</h1>
    </div>

    <!-- Success Icon -->
    <div class="ipaymu-success-icon">
        <svg width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
        </svg>
        <h2>Pembayaran Berhasil Dibuat</h2>
        <p>Silakan selesaikan pembayaran melalui salah satu metode di bawah ini</p>
    </div>

    <!-- Order Info -->
    <div class="ipaymu-order-info">
        <div class="ipaymu-order-info__row">
            <span class="ipaymu-order-info__label">Nomor Pesanan</span>
            <span class="ipaymu-order-info__value">{{ $order->order_number }}</span>
        </div>
        @if(isset($transaction['via']))
        <div class="ipaymu-order-info__row">
            <span class="ipaymu-order-info__label">Metode</span>
            <span class="ipaymu-order-info__value">{{ $transaction['via'] }} - {{ $transaction['channel'] }}</span>
        </div>
        @endif
        <div class="ipaymu-order-info__row ipaymu-order-info__row--amount">
            <span class="ipaymu-order-info__label">Total Pembayaran</span>
            <span class="ipaymu-order-info__value">Rp {{ number_format($transaction['amount'], 0, ',', '.') }}</span>
        </div>
        @if(isset($transaction['expired']))
        <div class="ipaymu-order-info__row">
            <span class="ipaymu-order-info__label">Berlaku Hingga</span>
            <span class="ipaymu-order-info__value">{{ \Carbon\Carbon::parse($transaction['expired'])->format('d M Y H:i') }} WIB</span>
        </div>
        @endif
    </div>

    <!-- QR Code -->
    @if(isset($transaction['qr_string']))
    <div class="ipaymu-qr-card">
        <div class="ipaymu-qr-card__header">
            <div class="ipaymu-qr-card__icon">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2Z"/>
                    <path d="M6 9.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5ZM3.5 7a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.5-.5Z" fill="white"/>
                </svg>
            </div>
            <h3 class="ipaymu-qr-card__title">QRIS</h3>
        </div>
        <div class="ipaymu-qr-card__body">
            <div class="ipaymu-qr-card__image" id="qrcode-container"></div>
            <p class="ipaymu-qr-card__instruction">
                Scan QR Code di atas menggunakan aplikasi e-wallet atau mobile banking yang mendukung QRIS.
            </p>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
    (function() {
        const qrString = '{{ $transaction['qr_string'] }}';
        const container = document.getElementById('qrcode-container');

        if (container && qrString) {
            new QRCode(container, {
                text: qrString,
                width: 280,
                height: 280,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }
    })();
    </script>
    @endif

    <!-- Payment Number (VA/Code) for non-QRIS -->
    @if(isset($transaction['payment_no']) && !isset($transaction['qr_string']))
    <div class="ipaymu-va-card">
        <div class="ipaymu-va-card__header">
            <div class="ipaymu-va-card__icon">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path d="m8 0 6.61 3h.89a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v7a.5.5 0 0 1 .485.38l.5 2a.498.498 0 0 1-.485.62H.5a.498.498 0 0 1-.485-.62l.5-2A.501.501 0 0 1 1 13V6H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 3h.89L8 0ZM3.777 3h8.447L8 1 3.777 3ZM2 6v7h1V6H2Zm2 0v7h2.5V6H4Zm3.5 0v7h1V6h-1Zm2 0v7H12V6H9.5ZM13 6v7h1V6h-1Zm2-1V4H1v1h14Zm-.39 9H1.39l-.25 1h13.72l-.25-1Z"/>
                </svg>
            </div>
            <h3 class="ipaymu-va-card__title">{{ $transaction['payment_name'] ?? 'Nomor Pembayaran' }}</h3>
        </div>
        <div class="ipaymu-va-card__body">
            <div class="ipaymu-va-card__label">{{ $transaction['via'] === 'VA' ? 'Nomor Virtual Account' : 'Kode Pembayaran' }}</div>
            <div class="ipaymu-va-card__number">
                <span id="payment-number">{{ $transaction['payment_no'] }}</span>
                <button type="button"
                        class="copy-button"
                        onclick="copyToClipboard('{{ $transaction['payment_no'] }}', this)"
                        aria-label="Copy nomor pembayaran">
                    <svg class="copy-button__icon copy-button__icon--copy" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                        <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                    </svg>
                    <svg class="copy-button__icon copy-button__icon--check" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                    </svg>
                </button>
            </div>
            <p class="ipaymu-va-card__instruction">
                @if($transaction['via'] === 'VA')
                Transfer ke nomor Virtual Account di atas melalui mobile banking, internet banking, atau ATM bank Anda.
                @else
                Gunakan kode pembayaran di atas untuk menyelesaikan transaksi Anda.
                @endif
            </p>
        </div>
    </div>
    @endif

    <!-- Payment URL -->
    @if(isset($transaction['payment_url']))
    <div class="ipaymu-link-card">
        <h3 class="ipaymu-link-card__title">Atau Bayar Melalui Link</h3>
        <a href="{{ $transaction['payment_url'] }}" target="_blank" class="btn btn--secondary btn--full">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                <path d="M6.354 5.5H4a3 3 0 0 0 0 6h3a3 3 0 0 0 2.83-4H9c-.086 0-.17.01-.25.031A2 2 0 0 1 7 10.5H4a2 2 0 1 1 0-4h1.535c.218-.376.495-.714.82-1z"/>
                <path d="M9 5.5a3 3 0 0 0-2.83 4h1.098A2 2 0 0 1 9 6.5h3a2 2 0 1 1 0 4h-1.535a4.02 4.02 0 0 1-.82 1H12a3 3 0 1 0 0-6H9z"/>
            </svg>
            Buka Halaman Pembayaran
        </a>
        <p class="ipaymu-link-card__note">
            Link pembayaran ini menyediakan berbagai metode pembayaran lainnya termasuk kartu kredit dan e-wallet.
        </p>
    </div>
    @endif

    <!-- Instructions -->
    <div class="ipaymu-instructions">
        <h3 class="ipaymu-instructions__title">Cara Pembayaran</h3>
        <ol class="ipaymu-instructions__list">
            <li>Pilih salah satu metode pembayaran di atas (Virtual Account, QRIS, atau Link)</li>
            <li>Selesaikan pembayaran sesuai nominal yang tertera</li>
            <li>Simpan bukti pembayaran Anda</li>
            <li>Pembayaran akan diverifikasi otomatis oleh sistem</li>
            <li>Status pesanan akan diupdate setelah pembayaran berhasil</li>
        </ol>
    </div>

    <!-- Actions -->
    <div class="ipaymu-result-actions">
        <a href="{{ route('customer.order.show', $order->id) }}" class="btn btn--primary btn--full">
            Kembali ke Detail Pesanan
        </a>
        <a href="https://wa.me/{{ \App\Models\Setting::get('store_whatsapp', '6281234567890') }}?text=Halo, saya telah melakukan pembayaran untuk pesanan {{ $order->order_number }}"
           class="btn btn--outline btn--full"
           target="_blank">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.309-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.348.1-.116.133-.217.199-.364.065-.15.033-.283-.033-.386-.065-.1-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
            </svg>
            Hubungi Customer Service
        </a>
    </div>
</div>

<script>
function copyToClipboard(text, button) {
    const tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();

    try {
        document.execCommand('copy');
        button.classList.add('copy-button--copied');
        setTimeout(() => {
            button.classList.remove('copy-button--copied');
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
        alert('Gagal menyalin nomor VA');
    }

    document.body.removeChild(tempInput);
}
</script>
@endsection
