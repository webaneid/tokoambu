@extends('storefront.layouts.app-mobile')

@section('content')
<div class="bank-transfer">
    <!-- Header -->
    <div class="bank-transfer-header">
        <a href="{{ $paymentRoutes['bankTransfer'] }}" class="bank-transfer-header__back">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5z"/>
            </svg>
        </a>
        <h1 class="bank-transfer-header__title">Konfirmasi Pembayaran</h1>
    </div>

    <!-- header payment confirmation -->
    <div class="bank-transfer-confirmation-header">
        <h2 class="bank-transfer-confirmation-header__title">Form Konfirmasi Pembayaran</h2>
        <p class="bank-transfer-confirmation-header__subtitle">Upload bukti transfer Anda untuk mempercepat verifikasi</p>
    </div>

    <!-- Payment Summary -->
    <div class="payment-amount-card">
        @if($isDpPayment)
        <div class="payment-amount-card__label">DP Preorder Minimal {{ \App\Models\Setting::getPreorderDpPercentage() }}%</div>
        <div class="payment-amount-card__amount">Rp {{ number_format($minimumDp, 0, ',', '.') }}</div>
        <div class="payment-amount-card__note">
            Dari total pembayaran Rp {{ number_format($paymentAmount, 0, ',', '.') }}, Nomor Order: {{ $order->order_number }}
        </div>
        @else
        <div class="payment-amount-card__label">Jumlah yang Harus Dibayar</div>
        <div class="payment-amount-card__amount">Rp {{ number_format($paymentAmount, 0, ',', '.') }}</div>
        <div class="payment-amount-card__note">
            Pembayaran untuk pesanan {{ $order->order_number }}
        </div>
        @endif
    </div>

    <!-- Payment Confirmation Form -->
    @if($bankAccounts->count() > 0)
    <div class="bank-transfer-form">
        
        <form action="{{ $paymentRoutes['bankTransferStore'] }}" method="POST" class="payment-form" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label class="form-label">Jumlah Pembayaran</label>
                @if($isDpPayment)
                    <input
                        type="number"
                        name="amount"
                        min="{{ $minimumDp ?? 0 }}"
                        step="0.01"
                        value="{{ old('amount', $minimumDp ?? $paymentAmount) }}"
                        class="form-input @error('amount') form-input-error @enderror"
                    >
                    <p class="form-helper">Minimal DP: Rp {{ number_format($minimumDp ?? 0, 0, ',', '.') }}</p>
                    @error('amount') <p class="form-error">{{ $message }}</p> @enderror
                @else
                    <input type="hidden" name="amount" value="{{ $paymentAmount }}">
                    <input
                        type="text"
                        value="Rp {{ number_format($paymentAmount, 0, ',', '.') }}"
                        readonly
                        class="form-input"
                    >
                @endif
            </div>

            <div class="form-group">
                <label class="form-label">Nama Pengirim (sesuai rekening) *</label>
                <input
                    type="text"
                    name="sender_name"
                    value="{{ old('sender_name') }}"
                    required
                    class="form-input @error('sender_name') form-input-error @enderror"
                    placeholder="Nama di rekening bank"
                >
                @error('sender_name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Bank Pengirim *</label>
                <input
                    type="text"
                    name="sender_bank"
                    value="{{ old('sender_bank') }}"
                    required
                    class="form-input @error('sender_bank') form-input-error @enderror"
                    placeholder="Contoh: BCA, Mandiri, BNI"
                >
                @error('sender_bank') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Transfer ke Rekening Toko *</label>
                <select
                    name="shop_bank_account_id"
                    required
                    class="form-input @error('shop_bank_account_id') form-input-error @enderror"
                >
                    <option value="">Pilih Rekening Toko</option>
                    @foreach($bankAccounts as $account)
                        <option
                            value="{{ $account->id }}"
                            {{ old('shop_bank_account_id') == $account->id ? 'selected' : '' }}
                        >
                            {{ $account->bank_name }} - {{ $account->account_number }} ({{ $account->account_name }})
                        </option>
                    @endforeach
                </select>
                @error('shop_bank_account_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Bukti Transfer *</label>
                <div class="file-upload">
                    <input
                        type="file"
                        name="payment_proof"
                        required
                        accept="image/*"
                        class="form-input @error('payment_proof') form-input-error @enderror"
                    >
                    <span class="file-upload__label">Format gambar (JPG/PNG/WebP), max 10MB.</span>
                </div>
                @error('payment_proof') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Catatan (Opsional)</label>
                <textarea
                    name="notes"
                    class="form-input @error('notes') form-input-error @enderror"
                    rows="3"
                    placeholder="Tambahkan catatan jika diperlukan"
                >{{ old('notes') }}</textarea>
                @error('notes') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn btn--primary btn--full">
                Kirim Konfirmasi Pembayaran
            </button>
        </form>
    </div>

    <!-- Actions -->
    <div class="bank-transfer-actions">
        <a href="{{ $paymentRoutes['bankTransfer'] }}" class="btn btn--outline btn--full">
            Kembali ke Rekening Toko
        </a>
        <a href="{{ route('customer.order.show', $order->id) }}" class="btn btn--outline btn--full">
            Kembali ke Detail Pesanan
        </a>
    </div>
    @endif
</div>
@endsection
