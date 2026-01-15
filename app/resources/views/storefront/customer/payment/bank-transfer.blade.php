@extends('storefront.layouts.app-mobile')

@section('content')
<div class="bank-transfer">
    <!-- Header -->
    <div class="bank-transfer-header">
        <a href="{{ $paymentRoutes['select'] }}" class="bank-transfer-header__back">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5z"/>
            </svg>
        </a>
        <h1 class="bank-transfer-header__title">Transfer Bank</h1>
    </div>

    <!-- Payment Summary -->
    <div class="payment-amount-card">
        @if($isDpPayment)
        <div class="payment-amount-card__label">DP Minimal {{ \App\Models\Setting::getPreorderDpPercentage() }}%</div>
        <div class="payment-amount-card__amount">Rp {{ number_format($minimumDp, 0, ',', '.') }}</div>
        
        <div class="payment-amount-card__note">
            dari  Rp {{ number_format($paymentAmount, 0, ',', '.') }} yang harus dibayarkan
        </div>
        <div class="payment-amount-card__note">
            Pesanan preorder nomor: {{ $order->order_number }}
        </div>
        @else
        <div class="payment-amount-card__label">Jumlah yang Harus Dibayar</div>
        <div class="payment-amount-card__amount">Rp {{ number_format($paymentAmount, 0, ',', '.') }}</div>
        
        <div class="payment-amount-card__note">
            Pembayaran untuk pesanan {{ $order->order_number }}
        </div>
        
        @endif
    </div>

    <!-- Instructions -->
    <div class="bank-transfer-instructions">
        <h2 class="bank-transfer-instructions__title">Cara Pembayaran</h2>
        <ol class="bank-transfer-instructions__list">
            <li>Pilih salah satu rekening toko di bawah ini</li>
            <li>Transfer sesuai jumlah yang tertera di atas</li>
            <li>Simpan bukti transfer Anda</li>
            <li>Klik tombol "Konfirmasi Pembayaran" di bawah untuk upload bukti transfer</li>
        </ol>
    </div>

    <!-- Bank Accounts List -->
    <div class="bank-accounts">
        <h2 class="bank-accounts__title">Rekening Toko</h2>

        @forelse($bankAccounts as $account)
        <div class="bank-account-card">
            <div class="bank-account-card__header">
                <div class="bank-account-card__bank-icon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="m8 0 6.61 3h.89a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v7a.5.5 0 0 1 .485.38l.5 2a.498.498 0 0 1-.485.62H.5a.498.498 0 0 1-.485-.62l.5-2A.501.501 0 0 1 1 13V6H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 3h.89L8 0ZM3.777 3h8.447L8 1 3.777 3ZM2 6v7h1V6H2Zm2 0v7h2.5V6H4Zm3.5 0v7h1V6h-1Zm2 0v7H12V6H9.5ZM13 6v7h1V6h-1Zm2-1V4H1v1h14Zm-.39 9H1.39l-.25 1h13.72l-.25-1Z"/>
                    </svg>
                </div>
                <div class="bank-account-card__bank-name">{{ $account->bank_name }}</div>
            </div>
            <div class="bank-account-card__body">
                <div class="bank-account-card__info">
                    <div class="bank-account-card__label">Nomor Rekening</div>
                    <div class="bank-account-card__value bank-account-card__value--number">
                        <span id="account-{{ $account->id }}">{{ $account->account_number }}</span>
                        <button type="button"
                                class="copy-button"
                                onclick="copyAccountNumber('{{ $account->account_number }}', {{ $account->id }})"
                                aria-label="Copy nomor rekening">
                            <svg class="copy-button__icon copy-button__icon--copy" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                                <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                            </svg>
                            <svg class="copy-button__icon copy-button__icon--check" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="bank-account-card__info">
                    <div class="bank-account-card__label">Atas Nama</div>
                    <div class="bank-account-card__value">{{ $account->account_name }}</div>
                </div>
            </div>
        </div>
        @empty
        <div class="bank-accounts__empty">
            <svg width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
            </svg>
            <p>Tidak ada rekening bank yang tersedia</p>
            <p class="bank-accounts__empty-hint">Silakan hubungi admin toko</p>
        </div>
        @endforelse
    </div>

    <!-- Contact WhatsApp Button -->
    @if($bankAccounts->count() > 0)
    <div class="bank-transfer-actions">
        <a href="https://wa.me/{{ \App\Models\Setting::get('store_whatsapp', '6281234567890') }}?text=Halo, saya ingin konfirmasi pembayaran untuk pesanan {{ $order->order_number }}"
           class="btn btn--outline btn--full"
           target="_blank">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.309-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.348.1-.116.133-.217.199-.364.065-.15.033-.283-.033-.386-.065-.1-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
            </svg>
            Konfirmasi via WhatsApp
        </a>
        <a href="{{ route('customer.order.show', $order->id) }}" class="btn btn--outline btn--full">
            Kembali ke Detail Pesanan
        </a>
    </div>

    <!-- Payment Confirmation Actions -->
    <div class="bank-transfer-actions-confirmation">
        <a href="{{ $paymentRoutes['bankTransferConfirm'] }}" class="btn btn--primary btn--full">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
            </svg>
            Konfirmasi Pembayaran
        </a>
        <p class="bank-transfer-actions-confirmation__text">Klik tombol di atas untuk upload bukti transfer Anda</p>
    </div>
    @endif
</div>

<script>
function copyAccountNumber(accountNumber, accountId) {
    // Create temporary input element
    const tempInput = document.createElement('input');
    tempInput.value = accountNumber;
    document.body.appendChild(tempInput);
    tempInput.select();

    try {
        // Copy to clipboard
        document.execCommand('copy');

        // Show success feedback
        const button = event.currentTarget;
        button.classList.add('copy-button--copied');

        // Reset after 2 seconds
        setTimeout(() => {
            button.classList.remove('copy-button--copied');
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
        alert('Gagal menyalin nomor rekening');
    }

    // Remove temporary input
    document.body.removeChild(tempInput);
}
</script>
@endsection
