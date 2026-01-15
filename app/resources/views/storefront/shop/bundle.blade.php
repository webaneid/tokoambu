@extends('storefront.layouts.app-mobile')

@section('title', $promotion->name . ' - ' . \App\Models\Setting::get('store_name', config('app.name')))

@push('styles')
<style>
/* Hide header and bottom nav for bundle detail page */
.app-header,
.search-bar,
.bottom-nav {
    display: none !important;
}

.storefront-main {
    padding-bottom: 0;
}
</style>
@endpush

@section('content')
<div class="product-detail">
    <div class="product-detail-header">
        <button type="button" class="product-detail-header__btn" onclick="window.history.back()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M7.28 7.72a.75.75 0 0 1 0 1.06l-2.47 2.47H21a.75.75 0 0 1 0 1.5H4.81l2.47 2.47a.75.75 0 1 1-1.06 1.06l-3.75-3.75a.75.75 0 0 1 0-1.06l3.75-3.75a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
            </svg>
        </button>
        <div class="product-detail-header__actions"></div>
    </div>

    <div class="product-gallery">
        <div class="product-gallery__container">
            @if ($image)
                <div class="product-gallery__slide">
                    <img src="{{ Storage::url($image->path) }}" alt="{{ $promotion->name }}">
                </div>
            @else
                <div class="product-gallery__slide">
                    <div class="product-gallery__placeholder">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </div>
                </div>
            @endif
            <span class="product-gallery__badge product-gallery__badge--preorder">Bundle</span>
        </div>
    </div>

    <div class="product-info">
        <h1 class="product-info__name">{{ $promotion->name }}</h1>
        @if ($promotion->description)
            <p class="text-sm text-gray-500">{{ $promotion->description }}</p>
        @endif
        <div class="product-info__price">
            <div class="product-card__price-original">
                Rp {{ number_format($pricing['original_total'], 0, ',', '.') }}
            </div>
            <div class="product-card__price">
                Rp {{ number_format($pricing['bundle_price'], 0, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="product-tabs">
        <div class="product-tabs__nav">
            <button class="product-tabs__tab active" data-tab="bundle-items">Isi Bundle</button>
        </div>
        <div class="product-tabs__content">
            <div class="product-tabs__pane active" data-pane="bundle-items">
                <div class="product-specs">
                    @foreach ($bundle->items as $bundleItem)
                        @php
                            $bundleProduct = $bundleItem->productVariant?->product ?? $bundleItem->product;
                            $bundleName = $bundleProduct?->name ?? 'Produk tidak tersedia';
                            $bundleVariant = $bundleItem->productVariant?->display_name;
                        @endphp
                        <div class="product-specs__row">
                            <span class="product-specs__label">{{ $bundleName }} @if($bundleVariant)<small>({{ $bundleVariant }})</small>@endif</span>
                            <span class="product-specs__value">x{{ $bundleItem->qty }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>

<div class="product-checkout">
    <div class="product-checkout__qty">
        <button type="button" class="product-checkout__qty-btn" id="qtyMinus">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M4.25 12a.75.75 0 0 1 .75-.75h14a.75.75 0 0 1 0 1.5H5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
            </svg>
        </button>
        <input type="number" class="product-checkout__qty-input" id="qtyInput" value="1" min="1" max="999" readonly>
        <button type="button" class="product-checkout__qty-btn" id="qtyPlus">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
    <button type="button" class="product-checkout__btn" id="addBundleBtn">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            <path d="M2.25 2.25a.75.75 0 0 0 0 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 0 0-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 0 0 0-1.5H5.378A2.25 2.25 0 0 1 7.5 15h11.218a.75.75 0 0 0 .674-.421 60.358 60.358 0 0 0 2.96-7.228.75.75 0 0 0-.525-.965A60.864 60.864 0 0 0 5.68 4.509l-.232-.867A1.875 1.875 0 0 0 3.636 2.25H2.25ZM3.75 20.25a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0ZM16.5 20.25a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z" />
        </svg>
        <span>Tambah ke Keranjang</span>
    </button>
</div>

@push('scripts')
<script>
const qtyInput = document.getElementById('qtyInput');
const qtyPlus = document.getElementById('qtyPlus');
const qtyMinus = document.getElementById('qtyMinus');

qtyPlus?.addEventListener('click', () => {
    const current = parseInt(qtyInput.value || '1', 10);
    qtyInput.value = Math.min(current + 1, 999);
});

qtyMinus?.addEventListener('click', () => {
    const current = parseInt(qtyInput.value || '1', 10);
    qtyInput.value = Math.max(current - 1, 1);
});

document.getElementById('addBundleBtn')?.addEventListener('click', async () => {
    const response = await fetch("{{ route('cart.bundle') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            bundle_id: {{ $bundle->id }},
            quantity: parseInt(qtyInput.value || '1', 10)
        })
    });

    const data = await response.json();
    if (data.success) {
        window.location.href = "{{ route('cart.index') }}";
        return;
    }

    alert(data.message || 'Gagal menambahkan bundle ke keranjang.');
});
</script>
@endpush
@endsection
