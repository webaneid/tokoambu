@extends('storefront.layouts.app-mobile')

@section('title', 'Keranjang Belanja - Toko Ambu')

@section('content')
<div class="storefront-app">
    <main class="storefront-main">
        <!-- Header -->
        <div class="cart-header">
            <a href="{{ route('shop.index') }}" class="back-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="cart-title">Keranjang Belanja</h1>
            <div style="width: 24px;"></div> <!-- Spacer for centering -->
        </div>

        @if (!$items->isEmpty())
            <!-- Cart Items List -->
            <div class="cart-items-list">
                @foreach ($items as $item)
                    <div class="cart-item-card" data-cart-item-id="{{ $item->id }}">
                        <!-- Product Image -->
                        <div class="cart-item-image">
                            @php
                                $isBundle = $item->bundle_id && $item->bundle;
                                $bundleItems = $item->bundle_items ?? [];
                                $bundleName = $item->bundle_name ?? $item->bundle?->promotion?->name ?? $item->product->name;

                                $imageUrl = null;
                                if ($isBundle) {
                                    $firstBundleItem = $item->bundle?->items?->first();
                                    $bundleMedia = $firstBundleItem?->productVariant?->featuredMedia ?? $firstBundleItem?->product?->featuredMedia;
                                    if ($bundleMedia) {
                                        $imageUrl = Storage::url($bundleMedia->path);
                                    }
                                } elseif ($item->variant && $item->variant->featuredMedia) {
                                    $imageUrl = Storage::url($item->variant->featuredMedia->path);
                                } elseif ($item->product->featuredMedia) {
                                    $imageUrl = Storage::url($item->product->featuredMedia->path);
                                }
                            @endphp

                            @if ($imageUrl)
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $item->product->name }}"
                                >
                            @else
                                <div class="cart-item-image-placeholder">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21 15 16 10 5 21"></polyline>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Product Info -->
                        <div class="cart-item-info">
                            <h3 class="cart-item-name">{{ $isBundle ? $bundleName : $item->product->name }}</h3>
                            @if($item->variant && ! $isBundle)
                                <p class="cart-item-variant">
                                    {{ implode(' / ', $item->variant->variant_attributes) }}
                                </p>
                            @endif
                            <p class="cart-item-sku">
                                {{ $isBundle ? 'Bundle promo' : ('SKU: ' . ($item->variant ? $item->variant->sku : $item->product->sku)) }}
                            </p>
                            @if ($isBundle)
                                <div class="cart-item-bundle-list">
                                    @foreach($bundleItems as $bundleItem)
                                        @php
                                            $bundleProduct = $bundleItem['item']->productVariant?->product ?? $bundleItem['item']->product;
                                            $bundleVariant = $bundleItem['item']->productVariant?->display_name;
                                        @endphp
                                        <div class="cart-item-bundle-list__item">
                                            {{ $bundleProduct?->name ?? '-' }}
                                            @if($bundleVariant)
                                                <small>({{ $bundleVariant }})</small>
                                            @endif
                                            ×{{ $bundleItem['qty'] }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @php
                                $unitPrice = $item->unit_price ?? $item->price;
                                $originalPrice = $item->original_price ?? null;
                            @endphp
                            @if ($originalPrice && $originalPrice > $unitPrice)
                                <span class="cart-item-badge {{ $isBundle ? 'cart-item-badge--bundle' : 'cart-item-badge--flash' }}">
                                    {{ $isBundle ? 'Bundle' : 'Flash Sale' }}
                                </span>
                                <p class="cart-item-price-original">Rp {{ number_format($originalPrice, 0, ',', '.') }}</p>
                            @endif
                            <p class="cart-item-price">Rp {{ number_format($unitPrice, 0, ',', '.') }}</p>
                        </div>

                        <!-- Delete Button -->
                        <button
                            class="cart-item-delete remove-item"
                            data-cart-item-id="{{ $item->id }}"
                            aria-label="Hapus item"
                        >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>

                        <!-- Quantity Controls -->
                        <div class="cart-item-quantity">
                            <button
                                class="qty-btn decrease-qty"
                                type="button"
                                data-cart-item-id="{{ $item->id }}"
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                            <input
                                type="number"
                                class="qty-input"
                                value="{{ $item->quantity }}"
                                min="1"
                                max="99"
                                data-cart-item-id="{{ $item->id }}"
                                readonly
                            >
                            <button
                                class="qty-btn increase-qty"
                                type="button"
                                data-cart-item-id="{{ $item->id }}"
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Cart Summary -->
            <div class="cart-summary">
                <!-- Discount Code (Optional) -->
                <div class="discount-code-section">
                    <input
                        type="text"
                        class="discount-input"
                        placeholder="Enter Discount Code"
                        id="coupon_code_input"
                        value="{{ $couponCode ?? '' }}"
                    >
                    <button class="discount-apply-btn">Apply</button>
                </div>
                <p class="discount-message" id="coupon-message">
                    @if ($couponPromotion)
                        Promo aktif: {{ $couponPromotion->name }}
                    @elseif (!empty($couponMessage))
                        {{ $couponMessage }}
                    @endif
                </p>

                <!-- Summary Details -->
                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value" id="subtotal-display">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>

                @if (!empty($couponDiscount) && $couponDiscount > 0)
                    <div class="summary-row">
                        <span class="summary-label">Diskon ({{ $couponPromotion?->name ?? 'Kupon' }})</span>
                        <span class="summary-value summary-discount">-Rp {{ number_format($couponDiscount, 0, ',', '.') }}</span>
                    </div>
                @endif

                <div class="summary-row total-row">
                    <span class="summary-label">Total</span>
                    <span class="summary-value total-value" id="total-display">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>

                <!-- Checkout Button -->
                @auth('customer')
                    <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-block btn-lg" style="margin-top: var(--ane-spacing-lg);">
                        Checkout
                    </a>
                @else
                    <a href="{{ route('customer.login') }}" class="btn btn-primary btn-block btn-lg" style="margin-top: var(--ane-spacing-lg);">
                        Login untuk Checkout
                    </a>
                @endauth
            </div>
        @else
            <!-- Empty Cart -->
            <div class="empty-cart">
                <svg width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" opacity="0.3">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <h3 class="empty-cart-title">Keranjang Kosong</h3>
                <p class="empty-cart-text">Mulai berbelanja dan tambahkan produk ke keranjang Anda</p>
                <a href="{{ route('shop.index') }}" class="btn btn-primary">
                    Mulai Belanja
                </a>
            </div>
        @endif
    </main>
</div>

@push('scripts')
<script>
// Increase quantity
document.querySelectorAll('.increase-qty').forEach(btn => {
    btn.addEventListener('click', function() {
        const cartItemId = this.dataset.cartItemId;
        const input = document.querySelector(`.qty-input[data-cart-item-id="${cartItemId}"]`);
        const newQty = parseInt(input.value) + 1;

        if (newQty <= 99) {
            input.value = newQty;
            updateCart(cartItemId, newQty);
        }
    });
});

// Decrease quantity
document.querySelectorAll('.decrease-qty').forEach(btn => {
    btn.addEventListener('click', function() {
        const cartItemId = this.dataset.cartItemId;
        const input = document.querySelector(`.qty-input[data-cart-item-id="${cartItemId}"]`);
        const newQty = parseInt(input.value) - 1;

        if (newQty >= 1) {
            input.value = newQty;
            updateCart(cartItemId, newQty);
        }
    });
});

// Direct quantity input change
document.querySelectorAll('.qty-input').forEach(input => {
    input.addEventListener('change', function() {
        const cartItemId = this.dataset.cartItemId;
        const newQty = parseInt(this.value);

        if (newQty >= 1 && newQty <= 99) {
            updateCart(cartItemId, newQty);
        } else {
            alert('Kuantitas harus antara 1 dan 99');
            location.reload();
        }
    });
});

// Remove item
document.querySelectorAll('.remove-item').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const cartItemId = this.dataset.cartItemId;

        if (confirm('Hapus produk dari keranjang?')) {
            removeFromCart(cartItemId);
        }
    });
});

// Apply coupon code
const couponBtn = document.querySelector('.discount-apply-btn');
if (couponBtn) {
    couponBtn.addEventListener('click', function () {
        const input = document.getElementById('coupon_code_input');
        const code = input ? input.value.trim() : '';
        const messageEl = document.getElementById('coupon-message');

        fetch('{{ route("cart.coupon.apply") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ code }),
        })
        .then(response => response.json().then(payload => ({ ok: response.ok, payload })))
        .then(({ ok, payload }) => {
            if (!ok) {
                if (messageEl) {
                    messageEl.textContent = payload.message || 'Kupon tidak valid.';
                }
                return;
            }
            if (messageEl) {
                messageEl.textContent = payload.message || 'Kupon diterapkan.';
            }
            location.reload();
        })
        .catch(() => {
            if (messageEl) {
                messageEl.textContent = 'Gagal menerapkan kupon.';
            }
        });
    });
}

// Update cart API call
function updateCart(cartItemId, quantity) {
    fetch('{{ route("cart.update") }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({
            cart_item_id: cartItemId,
            quantity: quantity,
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update totals in UI
            updateTotals(data.total);
            // Update item total
            location.reload(); // Simple reload for now
        } else {
            alert(data.message || 'Gagal memperbarui keranjang');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}

// Remove from cart API call
function removeFromCart(cartItemId) {
    fetch(`/cart/${cartItemId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove item from DOM
            const cartItem = document.querySelector(`[data-cart-item-id="${cartItemId}"]`);
            if (cartItem) {
                cartItem.remove();

                // If cart is now empty, reload page
                if (document.querySelectorAll('.cart-item-card').length === 0) {
                    location.reload();
                } else {
                    updateTotals(data.total);
                }
            }
        } else {
            alert(data.message || 'Gagal menghapus produk');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}

// Update totals display
function updateTotals(total) {
    document.getElementById('subtotal-display').textContent =
        'Rp ' + total.toLocaleString('id-ID', { minimumFractionDigits: 0 });
    document.getElementById('total-display').textContent =
        'Rp ' + total.toLocaleString('id-ID', { minimumFractionDigits: 0 });
}
</script>
@endpush
@endsection
