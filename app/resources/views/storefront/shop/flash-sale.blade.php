@extends('storefront.layouts.app-mobile')

@section('title', 'Flash Sale - ' . config('app.name'))
@section('seo_title', 'Flash Sale - ' . \App\Models\Setting::get('store_name', config('app.name')))
@section('seo_description', $flashSaleMaxDiscount ? 'Flash sale dengan diskon hingga ' . number_format($flashSaleMaxDiscount, 0) . '%.' : 'Flash sale dengan promo menarik hari ini.')
@section('seo_url', route('shop.flash-sale'))

@section('content')
<div class="shop-page flash-sale-page">
    <div class="flash-sale-page__header">
        <div>
            <h1>Flash Sale</h1>
            <p>
                @if($flashSaleMaxDiscount)
                    Diskon hingga {{ number_format($flashSaleMaxDiscount, 0) }}%
                @else
                    Promo khusus hari ini
                @endif
            </p>
            @if($flashSaleEndsAt)
                <div class="flash-sale-countdown" data-ends-at="{{ \Illuminate\Support\Carbon::parse($flashSaleEndsAt)->toIso8601String() }}">
                    Berakhir dalam <span class="flash-sale-countdown__value">--:--:--</span>
                </div>
            @endif
        </div>
    </div>

    @if($products->count() > 0)
        <div class="product-grid" data-next-url="{{ $products->nextPageUrl() }}">
            @foreach($products as $product)
                @php
                    $hasActivePreorder = $product->preorderPeriods->count() > 0;
                    $totalStock = $product->inventoryBalances->sum('qty_on_hand');
                    $reservedStock = $reservedQuantities->get($product->id, 0);
                    $availableStock = max(0, $totalStock - $reservedStock);
                    $flashSale = $flashSaleMap[$product->id] ?? ['has_flash_sale' => false];

                    if ($product->has_variants && $product->variants->count() > 0) {
                        $minPrice = $product->variants->min('selling_price');
                        $maxPrice = $product->variants->max('selling_price');
                        $priceRange = $minPrice != $maxPrice;
                    } else {
                        $price = $product->selling_price;
                    }
                @endphp

                <a href="{{ route('shop.show', $product->slug) }}" class="product-card">
                    <div class="product-card__image">
                        @if($product->featuredMedia)
                            <img src="{{ Storage::url($product->featuredMedia->path) }}"
                                 alt="{{ $product->name }}"
                                 loading="lazy">
                        @else
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Crect fill='%23f3f4f6' width='200' height='200'/%3E%3C/svg%3E"
                                 alt="No image">
                        @endif

                        @if(($flashSale['has_flash_sale'] ?? false))
                            <span class="product-card__badge product-card__badge--flash">Flash Sale</span>
                        @endif

                        @if($hasActivePreorder)
                            <span class="product-card__badge product-card__badge--preorder">Preorder</span>
                        @elseif($availableStock <= 0)
                            <span class="product-card__badge product-card__badge--out-of-stock">Habis</span>
                        @elseif($availableStock < 5)
                            <span class="product-card__badge product-card__badge--low-stock">Stok: {{ $availableStock }}</span>
                        @endif

                        <button class="product-card__wishlist" onclick="event.preventDefault(); toggleWishlist({{ $product->id }})">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="product-card__info">
                        @if($product->category)
                            <p class="product-card__category">{{ $product->category->name }}</p>
                        @endif

                        <h3 class="product-card__name">{{ $product->name }}</h3>

                        @if($product->has_variants && isset($priceRange) && $priceRange)
                            @if(($flashSale['has_flash_sale'] ?? false))
                                <div class="product-card__price-original">
                                    Rp {{ number_format($flashSale['original_price'] ?? $minPrice, 0, ',', '.') }}
                                </div>
                                <div class="product-card__price-range">
                                    <span class="from">Mulai</span> Rp {{ number_format($flashSale['sale_price'] ?? $minPrice, 0, ',', '.') }}
                                </div>
                            @else
                                <div class="product-card__price-range">
                                    <span class="from">Mulai</span> Rp {{ number_format($minPrice, 0, ',', '.') }}
                                </div>
                            @endif
                        @else
                            @if(($flashSale['has_flash_sale'] ?? false))
                                <div class="product-card__price-original">
                                    Rp {{ number_format($flashSale['original_price'] ?? $product->selling_price, 0, ',', '.') }}
                                </div>
                                <p class="product-card__price">Rp {{ number_format($flashSale['sale_price'] ?? $product->selling_price, 0, ',', '.') }}</p>
                            @else
                                <p class="product-card__price">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                            @endif
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        @if($products->hasPages())
            <div class="infinite-scroll-loader" hidden>Memuat...</div>
            <div class="infinite-scroll-sentinel"></div>
        @endif
    @else
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <div>
                <h3 class="empty-state__title">Promo Flash Sale Tidak Ditemukan</h3>
                <p class="empty-state__text">Belum ada produk flash sale aktif saat ini</p>
            </div>
        </div>
    @endif
</div>

<script>
function toggleWishlist(productId) {
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
}

const countdownEl = document.querySelector('.flash-sale-countdown');
if (countdownEl) {
    const endsAt = new Date(countdownEl.dataset.endsAt).getTime();
    const valueEl = countdownEl.querySelector('.flash-sale-countdown__value');

    const updateCountdown = () => {
        const now = Date.now();
        const diff = endsAt - now;

        if (diff <= 0) {
            valueEl.textContent = '00:00:00';
            return;
        }

        const totalSeconds = Math.floor(diff / 1000);
        const days = Math.floor(totalSeconds / 86400);
        const hours = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        const padded = (value) => String(value).padStart(2, '0');
        const timeString = `${padded(hours)}:${padded(minutes)}:${padded(seconds)}`;

        valueEl.textContent = days > 0 ? `${days} hari ${timeString}` : timeString;
    };

    updateCountdown();
    setInterval(updateCountdown, 1000);
}

const gridEl = document.querySelector('.product-grid');
const sentinelEl = document.querySelector('.infinite-scroll-sentinel');
const loaderEl = document.querySelector('.infinite-scroll-loader');
let isLoading = false;

const loadNextPage = async () => {
    if (!gridEl || !sentinelEl || isLoading) {
        return;
    }

    const nextUrl = gridEl.dataset.nextUrl;
    if (!nextUrl) {
        sentinelEl.remove();
        if (loaderEl) loaderEl.remove();
        return;
    }

    isLoading = true;
    if (loaderEl) loaderEl.hidden = false;

    try {
        const response = await fetch(nextUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const nextGrid = doc.querySelector('.product-grid');

        if (nextGrid) {
            Array.from(nextGrid.children).forEach((child) => {
                gridEl.appendChild(document.importNode(child, true));
            });
            gridEl.dataset.nextUrl = nextGrid.dataset.nextUrl || '';
        } else {
            gridEl.dataset.nextUrl = '';
        }
    } catch (error) {
        console.error('Failed to load more products', error);
    } finally {
        if (loaderEl) loaderEl.hidden = true;
        isLoading = false;
        if (!gridEl.dataset.nextUrl) {
            sentinelEl.remove();
            if (loaderEl) loaderEl.remove();
        }
    }
};

if (gridEl && sentinelEl) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                loadNextPage();
            }
        });
    }, { rootMargin: '200px' });

    observer.observe(sentinelEl);
}
</script>
@endsection

@php
    $seoProducts = $products->getCollection();
    $seoItemList = $seoProducts->values()->map(function ($product, $index) use ($products) {
        $position = (($products->currentPage() - 1) * $products->perPage()) + $index + 1;
        return [
            '@type' => 'ListItem',
            'position' => $position,
            'url' => route('shop.show', $product->slug),
            'name' => $product->name,
        ];
    });
@endphp

@push('seo')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => 'Flash Sale',
    'itemListElement' => $seoItemList,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
