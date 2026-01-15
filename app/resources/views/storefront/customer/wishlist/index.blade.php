@extends('storefront.layouts.app-mobile')

@section('title', 'Wishlist - ' . config('app.name'))

@section('content')
<div class="shop-page flash-sale-page">
    <div class="flash-sale-page__header">
        <div>
            <h1>Wishlist</h1>
            <p>Produk favorit yang ingin kamu beli</p>
        </div>
    </div>

    @if($wishlists->count() > 0)
        <div class="product-grid" data-next-url="{{ $wishlists->nextPageUrl() }}">
            @foreach($wishlists as $wishlist)
                @php
                    $product = $wishlist->product;
                    if (!$product) {
                        continue;
                    }

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
                            <svg viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="0">
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

        @if($wishlists->hasPages())
            <div class="infinite-scroll-loader" hidden>Memuat...</div>
            <div class="infinite-scroll-sentinel"></div>
        @endif
    @else
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 6.522a4.75 4.75 0 0 0-8.232-2.448l-.77 1.026-.77-1.026a4.75 4.75 0 0 0-8.232 2.448c-.217 1.47.214 2.975 1.189 4.085l7.813 8.952a.75.75 0 0 0 1.132 0l7.813-8.952a6.007 6.007 0 0 0 1.219-4.085Z" />
            </svg>
            <div>
                <h3 class="empty-state__title">Wishlist Masih Kosong</h3>
                <p class="empty-state__text">Simpan produk favorit kamu di sini</p>
            </div>
        </div>
    @endif
</div>

<script>
async function toggleWishlist(productId) {
    const token = document.querySelector('meta[name="csrf-token"]');
    try {
        const response = await fetch("{{ route('customer.wishlist.toggle') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token ? token.content : '',
            },
            body: JSON.stringify({ product_id: productId }),
        });

        if (response.status === 401) {
            window.location.href = "{{ route('customer.login') }}";
            return;
        }

        const data = await response.json();
        if (!response.ok) {
            alert(data.message || 'Gagal memperbarui wishlist.');
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

        if (!data.added) {
            setTimeout(() => window.location.reload(), 300);
        }
    } catch (error) {
        console.error('Wishlist error:', error);
        alert('Terjadi kesalahan saat memperbarui wishlist.');
    }
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
        console.error('Failed to load wishlist page', error);
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
