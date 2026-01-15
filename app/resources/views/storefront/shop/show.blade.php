@extends('storefront.layouts.app-mobile')

@section('title', $product->name . ' - ' . \App\Models\Setting::get('store_name', config('app.name')))
@php
    $storeName = \App\Models\Setting::get('store_name', config('app.name'));
    $seoDescription = \Illuminate\Support\Str::limit(strip_tags($product->description ?? ''), 160, '...');
    $seoDescription = $seoDescription !== '' ? $seoDescription : "Beli {$product->name} di {$storeName}.";
    $shareUrl = route('shop.show', $product->slug);
    $shareText = "{$product->name} • {$storeName}";
@endphp
@section('seo_title', $product->name . ' - ' . $storeName)
@section('seo_description', $seoDescription)
@section('seo_image', $product->featuredMedia ? Storage::url($product->featuredMedia->path) : '')
@section('seo_url', route('shop.show', $product->slug))
@section('seo_type', 'product')

@push('styles')
<style>
/* Hide header and bottom nav for product detail page */
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
    {{-- Product Detail Header --}}
    <div class="product-detail-header">
        <button type="button" class="product-detail-header__btn" onclick="window.history.back()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M7.28 7.72a.75.75 0 0 1 0 1.06l-2.47 2.47H21a.75.75 0 0 1 0 1.5H4.81l2.47 2.47a.75.75 0 1 1-1.06 1.06l-3.75-3.75a.75.75 0 0 1 0-1.06l3.75-3.75a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
            </svg>
        </button>

        <div class="product-detail-header__actions">
            <button
                type="button"
                class="product-detail-header__btn"
                id="shareBtn"
                data-share-url="{{ $shareUrl }}"
                data-share-text="{{ $shareText }}"
                data-share-title="{{ $product->name }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M15.75 4.5a3 3 0 1 1 .825 2.066l-8.421 4.679a3.002 3.002 0 0 1 0 1.51l8.421 4.679a3 3 0 1 1-.729 1.31l-8.421-4.678a3 3 0 1 1 0-4.132l8.421-4.679a3 3 0 0 1-.096-.755Z" clip-rule="evenodd" />
                </svg>
            </button>

            <button type="button" class="product-detail-header__btn" id="wishlistBtn" data-product-id="{{ $product->id }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Product Gallery --}}
    <div class="product-gallery">
        <div class="product-gallery__container">
            <div class="product-gallery__slider">
                {{-- Featured Image --}}
                @if ($product->featuredMedia)
                    <div class="product-gallery__slide" data-slide-index="0">
                        <img src="{{ Storage::url($product->featuredMedia->path) }}" alt="{{ $product->name }}" id="mainGalleryImage">
                    </div>
                @else
                    <div class="product-gallery__slide" data-slide-index="0">
                        <div class="product-gallery__placeholder">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                        </div>
                    </div>
                @endif

                {{-- Gallery Images --}}
                @if ($product->galleryMedia && $product->galleryMedia->count() > 0)
                    @foreach ($product->galleryMedia as $index => $media)
                        <div class="product-gallery__slide" data-slide-index="{{ $index + 1 }}">
                            <img src="{{ Storage::url($media->path) }}" alt="{{ $product->name }}">
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Stock/Preorder Badge --}}
            @php
                $hasActivePreorder = $product->preorderPeriods->count() > 0;
                $totalStock = $product->inventoryBalances->sum('qty_on_hand');
                $availableStock = max(0, $totalStock - $reservedQty);

                // Calculate total slides (featured + gallery)
                $totalSlides = 1 + ($product->galleryMedia ? $product->galleryMedia->count() : 0);
            @endphp

            @if ($hasActivePreorder)
                <span class="product-gallery__badge product-gallery__badge--preorder">Preorder</span>
            @elseif ($availableStock <= 0)
                <span class="product-gallery__badge product-gallery__badge--out">Stok Habis</span>
            @elseif ($availableStock < 5)
                <span class="product-gallery__badge product-gallery__badge--low">Stok: {{ $availableStock }}</span>
            @endif
        </div>

        {{-- Gallery Dots --}}
        @if ($totalSlides > 1)
            <div class="product-gallery__dots">
                @for ($i = 0; $i < $totalSlides; $i++)
                    <span class="product-gallery__dot {{ $i === 0 ? 'active' : '' }}" data-slide="{{ $i }}"></span>
                @endfor
            </div>
        @endif
    </div>

    {{-- Product Info --}}
    <div class="product-info">
        <h1 class="product-info__name">{{ $product->name }}</h1>

        {{-- Price --}}
        @if ($product->has_variants && $product->variants->count() > 0)
            @php
                $minPrice = $product->variants->min('selling_price');
                $maxPrice = $product->variants->max('selling_price');
            @endphp
            <div class="product-info__price" id="productPrice">
                @if ($minPrice == $maxPrice)
                    Rp {{ number_format($minPrice, 0, ',', '.') }}
                @else
                    Rp {{ number_format($minPrice, 0, ',', '.') }} - Rp {{ number_format($maxPrice, 0, ',', '.') }}
                @endif
            </div>
        @else
            <div class="product-info__price">
                Rp {{ number_format($product->selling_price, 0, ',', '.') }}
            </div>
        @endif

        {{-- Rating & Sales --}}
        <div class="product-info__meta">
            {{-- Hide rating temporarily until review system is implemented
            <div class="product-info__rating">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                </svg>
                <span>4.8</span>
            </div>
            <span class="product-info__divider">•</span>
            --}}
            <span class="product-info__sold">{{ number_format($totalSold ?? 0, 0) }} Terjual</span>
        </div>
    </div>

    {{-- Variant Selector --}}
    @if ($product->has_variants && $product->variant_groups && count($product->variant_groups) > 0)
        <div class="product-variants">
            @foreach($product->variant_groups as $groupIndex => $group)
                <div class="variant-group" data-group-name="{{ $group['name'] }}">
                    <label class="variant-group__label">{{ $group['name'] }}</label>
                    <div class="variant-options" id="variant-group-{{ $groupIndex }}">
                        @foreach($group['options'] as $option)
                            <button
                                type="button"
                                class="variant-option"
                                data-group="{{ $group['name'] }}"
                                data-option="{{ $option }}"
                                data-has-image="false">
                                <span class="variant-option__label">{{ $option }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Selected Variant Info --}}
            <div id="selectedVariantInfo" class="variant-info d-none">
                <div class="variant-info__row">
                    <span class="variant-info__label">SKU:</span>
                    <span class="variant-info__value" id="variantSku">-</span>
                </div>
                <div class="variant-info__row">
                    <span class="variant-info__label">Harga:</span>
                    <span class="variant-info__value variant-info__value--price" id="variantPrice">-</span>
                </div>
                <div class="variant-info__row">
                    <span class="variant-info__label">Stok:</span>
                    <span class="variant-info__value" id="variantStock">-</span>
                </div>
            </div>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="product-tabs">
        <div class="product-tabs__nav">
            <button class="product-tabs__tab active" data-tab="description">Deskripsi</button>
            <button class="product-tabs__tab" data-tab="specs">Spesifikasi</button>
            <button class="product-tabs__tab" data-tab="reviews">Ulasan</button>
        </div>

        <div class="product-tabs__content">
            {{-- Description Tab --}}
            <div class="product-tabs__pane active" data-pane="description">
                @if ($product->description)
                    <div class="product-description">
                        {!! $product->description !!}
                    </div>
                @else
                    <p class="product-tabs__empty">Belum ada deskripsi untuk produk ini.</p>
                @endif
            </div>

            {{-- Specifications Tab --}}
            <div class="product-tabs__pane" data-pane="specs">
                <div class="product-specs">
                    <div class="product-specs__row">
                        <span class="product-specs__label">Kategori</span>
                        <span class="product-specs__value">{{ $product->category->name ?? 'N/A' }}</span>
                    </div>
                    <div class="product-specs__row">
                        <span class="product-specs__label">SKU</span>
                        <span class="product-specs__value">{{ $product->sku }}</span>
                    </div>
                    @if (!$product->has_variants)
                        <div class="product-specs__row">
                            <span class="product-specs__label">Stok Tersedia</span>
                            <span class="product-specs__value">{{ number_format($availableStock, 0) }} unit</span>
                        </div>
                    @endif
                    @if ($product->weight_grams)
                        <div class="product-specs__row">
                            <span class="product-specs__label">Berat</span>
                            <span class="product-specs__value">{{ number_format($product->weight_grams / 1000, 2) }} kg</span>
                        </div>
                    @endif

                    {{-- Custom Fields from Category --}}
                    @if ($product->category && $product->category->custom_fields && is_array($product->category->custom_fields))
                        @foreach ($product->category->custom_fields as $field)
                            @php
                                $fieldId = $field['id'] ?? null;
                                $fieldLabel = $field['label'] ?? '';
                                $fieldValue = $product->custom_field_values[$fieldId] ?? null;
                            @endphp
                            @if ($fieldLabel && $fieldValue)
                                <div class="product-specs__row">
                                    <span class="product-specs__label">{{ $fieldLabel }}</span>
                                    <span class="product-specs__value">{{ $fieldValue }}</span>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Reviews Tab --}}
            <div class="product-tabs__pane" data-pane="reviews">
                <p class="product-tabs__empty">Belum ada ulasan untuk produk ini.</p>
            </div>
        </div>
    </div>

    {{-- Related Products --}}
    @if ($relatedProducts->count() > 0)
        <div class="related-products">
            <h3 class="related-products__title">Produk Terkait</h3>
            <div class="related-products__grid">
                @foreach ($relatedProducts as $related)
                    <a href="{{ route('shop.show', $related->slug) }}" class="product-card-mini">
                        <div class="product-card-mini__image">
                            @if ($related->featuredMedia)
                                <img src="{{ Storage::url($related->featuredMedia->path) }}" alt="{{ $related->name }}">
                            @else
                                <div class="product-card-mini__placeholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="product-card-mini__info">
                            <h4 class="product-card-mini__name">{{ $related->name }}</h4>
                            @if ($related->has_variants && $related->variants->count() > 0)
                                @php
                                    $minPrice = $related->variants->min('selling_price');
                                    $maxPrice = $related->variants->max('selling_price');
                                @endphp
                                <div class="product-card-mini__price">
                                    @if ($minPrice == $maxPrice)
                                        Rp {{ number_format($minPrice, 0, ',', '.') }}
                                    @else
                                        Rp {{ number_format($minPrice, 0, ',', '.') }} - Rp {{ number_format($maxPrice, 0, ',', '.') }}
                                    @endif
                                </div>
                            @else
                                <div class="product-card-mini__price">
                                    Rp {{ number_format($related->selling_price, 0, ',', '.') }}
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- Fixed Footer Checkout --}}
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
    @if($product->allow_preorder)
        <button type="button" class="product-checkout__btn product-checkout__btn--preorder" id="addToCartBtn" @if($product->has_variants) disabled @endif>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clip-rule="evenodd" />
            </svg>
            <span>Preorder Sekarang</span>
        </button>
    @else
        <button type="button" class="product-checkout__btn" id="addToCartBtn" @if($product->has_variants) disabled @endif>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M2.25 2.25a.75.75 0 0 0 0 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 0 0-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 0 0 0-1.5H5.378A2.25 2.25 0 0 1 7.5 15h11.218a.75.75 0 0 0 .674-.421 60.358 60.358 0 0 0 2.96-7.228.75.75 0 0 0-.525-.965A60.864 60.864 0 0 0 5.68 4.509l-.232-.867A1.875 1.875 0 0 0 3.636 2.25H2.25ZM3.75 20.25a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0ZM16.5 20.25a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z" />
            </svg>
            <span>Tambah ke Keranjang</span>
        </button>
    @endif
</div>
@endsection

@php
    $seoStockTotal = $product->inventoryBalances->sum('qty_on_hand');
    $seoReserved = $reservedQty ?? 0;
    $seoAvailableStock = max(0, $seoStockTotal - $seoReserved);
    $seoPrice = $product->has_variants && $product->variants->count() > 0
        ? $product->variants->min('selling_price')
        : $product->selling_price;
    $seoImage = $product->featuredMedia ? Storage::url($product->featuredMedia->path) : null;
@endphp

@push('seo')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product->name,
    'image' => $seoImage ? [$seoImage] : [],
    'description' => strip_tags($product->description ?? ''),
    'sku' => $product->sku,
    'brand' => [
        '@type' => 'Brand',
        'name' => \App\Models\Setting::get('store_name', config('app.name')),
    ],
    'offers' => [
        '@type' => 'Offer',
        'priceCurrency' => 'IDR',
        'price' => $seoPrice,
        'availability' => $seoAvailableStock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url' => route('shop.show', $product->slug),
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('scripts')
<script>
// Share button handler
(function() {
    const shareBtn = document.getElementById('shareBtn');
    if (!shareBtn) return;

    shareBtn.addEventListener('click', function() {
        const payload = {
            url: this.dataset.shareUrl,
            text: this.dataset.shareText,
            title: this.dataset.shareTitle
        };

        if (typeof window.showShareModal === 'function') {
            window.showShareModal(payload);
            return;
        }

        if (navigator.share) {
            navigator.share({
                title: payload.title,
                text: payload.text,
                url: payload.url
            }).catch(() => {});
            return;
        }

        alert('Fitur share akan segera hadir!');
    });
})();

// Wishlist button handler
document.getElementById('wishlistBtn').addEventListener('click', function() {
    const btn = this;
    const svg = btn.querySelector('svg');
    const productId = btn.dataset.productId;
    const token = document.querySelector('meta[name="csrf-token"]');

    fetch("{{ route('customer.wishlist.toggle') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token ? token.content : '',
        },
        body: JSON.stringify({ product_id: productId }),
    })
        .then(async (response) => {
            if (response.status === 401) {
                window.location.href = "{{ route('customer.login') }}";
                return null;
            }
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Gagal memperbarui wishlist.');
            }
            return data;
        })
        .then((data) => {
            if (!data) {
                return;
            }
            if (data.added) {
                svg.setAttribute('fill', 'currentColor');
                svg.setAttribute('stroke-width', '0');
            } else {
                svg.setAttribute('fill', 'none');
                svg.setAttribute('stroke-width', '2');
            }
            document.querySelectorAll('[data-wishlist-count]').forEach((badge) => {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = '';
                } else {
                    badge.textContent = '';
                    badge.style.display = 'none';
                }
            });
            const message = data.added ? 'Produk ditambahkan ke wishlist' : 'Produk dihapus dari wishlist';
            if (typeof showWishlistSuccessModal === 'function') {
                showWishlistSuccessModal(message);
            } else {
                alert(message);
            }
        })
        .catch((error) => {
            console.error('Wishlist error:', error);
            alert('Terjadi kesalahan saat memperbarui wishlist.');
        });
});

// Gallery slider functionality
(function() {
    const slider = document.querySelector('.product-gallery__slider');
    const slides = document.querySelectorAll('.product-gallery__slide');
    const dots = document.querySelectorAll('.product-gallery__dot');
    const container = document.querySelector('.product-gallery__container');

    if (!slider || slides.length === 0) return;

    let currentSlide = 0;
    let touchStartX = 0;
    let touchCurrentX = 0;
    let isDragging = false;
    let startTransform = 0;

    function goToSlide(index, animate = true) {
        if (index < 0 || index >= slides.length) return;

        currentSlide = index;
        const offset = -currentSlide * 100;

        if (animate) {
            slider.style.transition = 'transform 300ms ease-out';
        } else {
            slider.style.transition = 'none';
        }

        slider.style.transform = `translateX(${offset}%)`;

        // Update dots
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === currentSlide);
        });
    }

    // Touch events for swipe with visual feedback
    container.addEventListener('touchstart', e => {
        touchStartX = e.touches[0].clientX;
        touchCurrentX = touchStartX;
        isDragging = true;
        startTransform = -currentSlide * 100;

        // Disable transition during drag
        slider.style.transition = 'none';
    });

    container.addEventListener('touchmove', e => {
        if (!isDragging) return;

        touchCurrentX = e.touches[0].clientX;
        const diff = touchCurrentX - touchStartX;
        const containerWidth = container.offsetWidth;
        const diffPercent = (diff / containerWidth) * 100;

        // Apply transform with drag
        let newTransform = startTransform + diffPercent;

        // Add resistance at edges
        if (currentSlide === 0 && diffPercent > 0) {
            newTransform = startTransform + (diffPercent * 0.3); // Resistance
        } else if (currentSlide === slides.length - 1 && diffPercent < 0) {
            newTransform = startTransform + (diffPercent * 0.3); // Resistance
        }

        slider.style.transform = `translateX(${newTransform}%)`;
    });

    container.addEventListener('touchend', e => {
        if (!isDragging) return;
        isDragging = false;

        const diff = touchStartX - touchCurrentX;
        const swipeThreshold = 50;

        // Re-enable transition
        slider.style.transition = 'transform 300ms ease-out';

        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0 && currentSlide < slides.length - 1) {
                // Swipe left - next slide
                goToSlide(currentSlide + 1);
            } else if (diff < 0 && currentSlide > 0) {
                // Swipe right - previous slide
                goToSlide(currentSlide - 1);
            } else {
                // Not enough to change, snap back
                goToSlide(currentSlide);
            }
        } else {
            // Snap back to current slide
            goToSlide(currentSlide);
        }
    });

    // Handle touchcancel
    container.addEventListener('touchcancel', e => {
        if (!isDragging) return;
        isDragging = false;
        slider.style.transition = 'transform 300ms ease-out';
        goToSlide(currentSlide);
    });

    // Dot click
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            goToSlide(index, true);
        });
    });

    // Initialize
    goToSlide(0, false);
})();

// Tab functionality
(function() {
    const tabs = document.querySelectorAll('.product-tabs__tab');
    const panes = document.querySelectorAll('.product-tabs__pane');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;

            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));

            this.classList.add('active');
            document.querySelector(`[data-pane="${targetTab}"]`).classList.add('active');
        });
    });
})();

// Quantity controls
(function() {
    const qtyInput = document.getElementById('qtyInput');
    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus = document.getElementById('qtyPlus');

    qtyMinus.addEventListener('click', () => {
        const currentQty = parseInt(qtyInput.value);
        if (currentQty > 1) {
            qtyInput.value = currentQty - 1;
        }
    });

    qtyPlus.addEventListener('click', () => {
        const currentQty = parseInt(qtyInput.value);
        const maxQty = parseInt(qtyInput.max);
        if (currentQty < maxQty) {
            qtyInput.value = currentQty + 1;
        }
    });
})();

@if ($product->has_variants && $product->variant_groups && count($product->variant_groups) > 0)
// Variant selection handler
(function() {
    const variants = @json($product->variants);
    const variantReservedQty = @json($variantReservedQty);
    const selectedOptions = {};
    let selectedVariantId = null;
    const variantInfoPanel = document.getElementById('selectedVariantInfo');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const qtyInput = document.getElementById('qtyInput');
    const mainGalleryImage = document.getElementById('mainGalleryImage');
    const productPrice = document.getElementById('productPrice');
    const featuredImageUrl = @json($product->featuredMedia ? Storage::url($product->featuredMedia->path) : null);
    const variantImageMap = new Map();

    // Load variant images and add thumbnails to buttons
    function loadVariantImages() {
        // Step 1: Detect which groups have DIFFERENT images per option
        // (e.g., Warna group has different images for Hijau, Pink, etc.)
        const groupImageCounts = {};

        variants.forEach(variant => {
            if (variant.variant_image_id && variant.variant_image) {
                for (const [groupName, optionValue] of Object.entries(variant.variant_attributes)) {
                    if (!groupImageCounts[groupName]) {
                        groupImageCounts[groupName] = new Set();
                    }
                    groupImageCounts[groupName].add(variant.variant_image_id);
                }
            }
        });

        // Determine which group has variant images (the one with multiple different images)
        let imageGroup = null;
        for (const [groupName, imageIds] of Object.entries(groupImageCounts)) {
            if (imageIds.size > 1) {
                // This group has different images for different options
                imageGroup = groupName;
                break;
            }
        }

        // Step 2: ONLY map images for the group that has variant images
        variants.forEach(variant => {
            if (variant.variant_image_id && variant.variant_image && imageGroup) {
                const imageUrl = variant.variant_image.url || `/storage/${variant.variant_image.path}`;
                const optionValue = variant.variant_attributes[imageGroup];

                if (optionValue) {
                    const key = `${imageGroup}:${optionValue}`;

                    // Only map once per option
                    if (!variantImageMap.has(key)) {
                        variantImageMap.set(key, imageUrl);
                    }
                }
            }
        });

        // Step 3: Add thumbnails ONLY to buttons in the image group
        variantImageMap.forEach((imageUrl, key) => {
            const [groupName, optionValue] = key.split(':');
            const btn = document.querySelector(
                `.variant-option[data-group="${groupName}"][data-option="${optionValue}"]`
            );

            // Only add thumbnail if button exists and doesn't already have image
            if (btn && btn.dataset.hasImage === 'false') {
                addThumbnailToButton(btn, imageUrl, optionValue);
            }
        });
    }

    function addThumbnailToButton(btn, imageUrl, altText) {
        btn.dataset.hasImage = 'true';
        btn.classList.add('variant-option--with-image');

        // Create thumbnail
        const thumbnail = document.createElement('div');
        thumbnail.className = 'variant-option__thumbnail';
        const img = document.createElement('img');
        img.src = imageUrl;
        img.alt = altText;
        thumbnail.appendChild(img);

        // Insert thumbnail before label
        const label = btn.querySelector('.variant-option__label');
        btn.insertBefore(thumbnail, label);
    }

    // Initialize variant images
    loadVariantImages();

    // Handle variant option selection
    document.querySelectorAll('.variant-option').forEach(btn => {
        btn.addEventListener('click', function() {
            const group = this.dataset.group;
            const option = this.dataset.option;

            // Deselect others in the same group
            document.querySelectorAll(`.variant-option[data-group="${group}"], .variant-color[data-group="${group}"]`).forEach(b => {
                b.classList.remove('active');
            });

            // Select this option
            this.classList.add('active');
            selectedOptions[group] = option;

            // Find matching variant
            updateVariantInfo();
        });
    });

    function updateVariantInfo() {
        // Find variant that matches all selected options
        const matchedVariant = variants.find(variant => {
            return Object.entries(selectedOptions).every(([group, option]) => {
                return variant.variant_attributes[group] === option;
            });
        });

        if (matchedVariant) {
            selectedVariantId = matchedVariant.id;

            // Calculate available stock
            const totalStock = matchedVariant.inventory_balances
                ? matchedVariant.inventory_balances.reduce((sum, balance) => sum + balance.qty_on_hand, 0)
                : 0;
            const reservedStock = variantReservedQty[matchedVariant.id] || 0;
            const availableStock = Math.max(0, totalStock - reservedStock);

            // Update variant info panel
            variantInfoPanel.classList.remove('d-none');
            document.getElementById('variantSku').textContent = matchedVariant.sku;
            document.getElementById('variantPrice').textContent = `Rp ${formatNumber(matchedVariant.selling_price)}`;
            document.getElementById('variantStock').textContent = `${availableStock} unit`;

            // Update price display
            productPrice.textContent = `Rp ${formatNumber(matchedVariant.selling_price)}`;

            // Update main image if variant has image
            if (matchedVariant.variant_image_id && matchedVariant.variant_image) {
                const variantImageUrl = matchedVariant.variant_image.url || `/storage/${matchedVariant.variant_image.path}`;
                if (mainGalleryImage) {
                    mainGalleryImage.src = variantImageUrl;
                }
            } else if (featuredImageUrl && mainGalleryImage) {
                mainGalleryImage.src = featuredImageUrl;
            }

            // Update quantity input max and button state
            const isPreorder = {{ $product->allow_preorder ? 'true' : 'false' }};

            if (isPreorder) {
                qtyInput.max = 999;
                addToCartBtn.disabled = false;
                addToCartBtn.classList.remove('product-checkout__btn--disabled');
            } else {
                qtyInput.max = availableStock;
                if (parseInt(qtyInput.value) > availableStock) {
                    qtyInput.value = Math.max(1, availableStock);
                }

                if (availableStock <= 0) {
                    addToCartBtn.disabled = true;
                    addToCartBtn.classList.add('product-checkout__btn--disabled');
                } else {
                    addToCartBtn.disabled = false;
                    addToCartBtn.classList.remove('product-checkout__btn--disabled');
                }
            }
        } else {
            selectedVariantId = null;
            variantInfoPanel.classList.add('d-none');
            addToCartBtn.disabled = true;
            addToCartBtn.classList.add('product-checkout__btn--disabled');
        }
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    // Add to cart for variant products
    addToCartBtn.addEventListener('click', function(e) {
        e.preventDefault();

        if (!selectedVariantId) {
            alert('Silakan pilih semua variasi terlebih dahulu');
            return;
        }

        const quantity = parseInt(qtyInput.value);
        const btn = this;
        const originalHTML = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<span>Menambahkan...</span>';

        fetch('{{ route("cart.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                product_id: {{ $product->id }},
                variant_id: selectedVariantId,
                quantity: quantity,
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const isPreorder = {{ $product->allow_preorder ? 'true' : 'false' }};
                if (isPreorder) {
                    showCartSuccessModal('Produk preorder berhasil ditambahkan ke keranjang', true);
                } else {
                    showCartSuccessModal('Produk berhasil ditambahkan ke keranjang', false);
                }
                qtyInput.value = 1;
            } else {
                alert(data.message || 'Gagal menambahkan ke keranjang');
            }
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menambahkan ke keranjang');
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        });
    });
})();
@else
// Add to cart for simple products
(function() {
    const availableStock = {{ $availableStock }};
    const isPreorder = {{ $product->allow_preorder ? 'true' : 'false' }};
    const addToCartBtn = document.getElementById('addToCartBtn');
    const qtyInput = document.getElementById('qtyInput');

    // For preorder products, allow higher quantity (e.g., 999)
    // For regular products, limit to available stock
    if (isPreorder) {
        qtyInput.max = 999;
    } else {
        qtyInput.max = availableStock;

        // Disable button only if stock is 0 AND not preorder
        if (availableStock <= 0) {
            addToCartBtn.disabled = true;
            addToCartBtn.classList.add('product-checkout__btn--disabled');
        }
    }

    addToCartBtn.addEventListener('click', function(e) {
        e.preventDefault();

        // For regular products (not preorder), check stock
        if (!isPreorder && availableStock <= 0) {
            alert('Maaf, produk ini sedang tidak tersedia');
            return;
        }

        const quantity = parseInt(qtyInput.value);
        const btn = this;
        const originalHTML = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<span>Menambahkan...</span>';

        fetch('{{ route("cart.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                product_id: {{ $product->id }},
                quantity: quantity,
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (isPreorder) {
                    showCartSuccessModal('Produk preorder berhasil ditambahkan ke keranjang', true);
                } else {
                    showCartSuccessModal('Produk berhasil ditambahkan ke keranjang', false);
                }
                qtyInput.value = 1;
            } else {
                alert(data.message || 'Gagal menambahkan ke keranjang');
            }
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menambahkan ke keranjang');
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        });
    });
})();
@endif
</script>
@endpush
