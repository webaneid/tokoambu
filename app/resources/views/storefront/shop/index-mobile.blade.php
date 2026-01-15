@extends('storefront.layouts.app-mobile')

@section('title', 'Shop - ' . config('app.name'))
@section('seo_title', 'Shop - ' . \App\Models\Setting::get('store_name', config('app.name')))
@section('seo_description', 'Jelajahi produk pilihan, promo menarik, dan rekomendasi terbaik di toko kami.')
@section('seo_url', route('shop.index'))

@section('content')
<div class="shop-page">
    {{-- Hero Banner --}}
    @php
        $heroBanners = collect($heroBanners ?? []);
        if ($heroBanners->isEmpty()) {
            $heroBanners = collect([[
                'type' => 'text',
                'title' => 'Super Sale',
                'description' => 'Up to 50%',
                'link' => '/shop',
                'link_text' => 'Shop Now',
            ]]);
        }
    @endphp
    <div class="hero-banner" data-hero-carousel>
        <div class="hero-banner__track" data-hero-track>
            @foreach($heroBanners as $index => $banner)
                @php
                    $bannerType = $banner['type'] ?? 'image';
                    $bannerTitle = $banner['title'] ?? 'Promo Spesial';
                    $bannerDescription = $banner['description'] ?? null;
                    $bannerLink = $banner['link'] ?? '/shop';
                    $bannerLinkText = $banner['link_text'] ?? 'Shop Now';
                    $bannerImage = $banner['image_url'] ?? null;
                @endphp
                <div class="hero-banner__slide {{ $index === 0 ? 'is-active' : '' }} {{ $bannerType === 'image' ? 'hero-banner__slide--image' : '' }}">
                    @if($bannerType === 'image' && $bannerImage)
                        <a href="{{ $bannerLink }}" class="hero-banner__image-link" aria-label="{{ $bannerTitle }}">
                            <img class="hero-banner__image" src="{{ $bannerImage }}" alt="{{ $bannerTitle }}">
                        </a>
                    @else
                        <div class="hero-banner__content">
                            <h1 class="hero-banner__title">
                                {{ $bannerTitle }}
                                @if($bannerDescription)
                                    <span class="highlight">{{ $bannerDescription }}</span>
                                @endif
                            </h1>
                            <a href="{{ $bannerLink }}" class="hero-banner__cta">
                                {{ $bannerLinkText }}
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        {{-- Dots indicator --}}
        @if($heroBanners->count() > 1)
            <div class="hero-banner__dots">
                @foreach($heroBanners as $index => $banner)
                    <span class="hero-banner__dot {{ $index === 0 ? 'active' : '' }}" data-hero-dot="{{ $index }}"></span>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Category Circles --}}
    @php
        $activeCategory = request()->routeIs('shop.category') ? request()->route('category') : null;
    @endphp
    <div class="category-circles">
        <a href="{{ route('shop.index') }}" class="category-circle {{ $activeCategory ? '' : 'active' }}">
            <div class="category-circle__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                </svg>
            </div>
            <span class="category-circle__label">Semua</span>
        </a>
        @foreach($categories->take(6) as $category)
            <a href="{{ route('shop.category', $category->slug) }}"
               class="category-circle {{ $activeCategory && $activeCategory->id === $category->id ? 'active' : '' }}">
                <div class="category-circle__icon">
                    <span class="category-circle__initial">
                        {{ strtoupper(substr($category->name, 0, 2)) }}
                    </span>
                </div>
                <span class="category-circle__label">{{ $category->name }}</span>
            </a>
        @endforeach
    </div>

    @if($flashSaleProducts->count() > 0)
        <div class="flash-sale-section">
            <div class="flash-sale-header">
                <div class="flash-sale-header__meta">
                    <span class="flash-sale-pill">
                        <svg class="flash-sale-pill__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.25 2.25a.75.75 0 0 1 .67.402l5.5 11a.75.75 0 0 1-.67 1.098H13.5l1.995 6.65a.75.75 0 0 1-1.341.661l-7.5-10.5a.75.75 0 0 1 .611-1.188h3.33l.879-7.04a.75.75 0 0 1 .776-.683Z" />
                        </svg>
                        Flash Sale
                    </span>
                    <h2 class="flash-sale-title">{{ $flashSaleTitle ?: 'Promo Flash Sale' }}</h2>
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
                <a href="{{ route('shop.flash-sale') }}">Lihat semua</a>
            </div>
            <div class="flash-sale-carousel">
                @foreach($flashSaleProducts as $product)
                    @php
                        $flashSale = $flashSaleMap[$product->id] ?? ['has_flash_sale' => false];
                    @endphp
                    <a href="{{ route('shop.show', $product->slug) }}" class="product-card flash-sale-card">
                        <div class="product-card__image">
                            @if($product->featuredMedia)
                                <img src="{{ Storage::url($product->featuredMedia->path) }}"
                                     alt="{{ $product->name }}"
                                     loading="lazy">
                            @else
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Crect fill='%23f3f4f6' width='200' height='200'/%3E%3C/svg%3E"
                                     alt="No image">
                            @endif

                            <span class="product-card__badge product-card__badge--flash">Flash Sale</span>
                        </div>
                        <div class="product-card__info">
                            @if($product->category)
                                <p class="product-card__category">{{ $product->category->name }}</p>
                            @endif
                            <h3 class="product-card__name">{{ $product->name }}</h3>
                            <div class="product-card__price-original">
                                Rp {{ number_format($flashSale['original_price'] ?? $product->selling_price, 0, ',', '.') }}
                            </div>
                            <p class="product-card__price">
                                Rp {{ number_format($flashSale['sale_price'] ?? $product->selling_price, 0, ',', '.') }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="sr-only" data-debug-bundle-count="{{ $bundleCards->count() }}" data-debug-product-count="{{ $products->count() }}" data-debug-mixed-count="{{ $mixedItems->count() }}"></div>

    {{-- Section Title --}}
    <div class="section-title">
        <h2>Special For You</h2>
        <a href="{{ route('shop.all') }}">
            See all
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </a>
    </div>

    {{-- Product Grid --}}
    @if($products->count() > 0 || $bundleCards->count() > 0)
        @php
            $productCollection = $products->getCollection();
            $firstProducts = $productCollection->take(10);
            $secondProducts = $productCollection->slice(10);
        @endphp

        @if($firstProducts->count() > 0)
            <div class="product-grid">
                @foreach($firstProducts as $product)
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
        @endif

        @if($bundleCards->count() > 0)
            <section class="flash-sale-section flash-sale-section--bundle">
                <div class="flash-sale-header">
                    <div class="flash-sale-header__meta">
                        <span class="flash-sale-pill">
                            <svg class="flash-sale-pill__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/>
                            </svg>
                            Bundle
                        </span>
                        <h2 class="flash-sale-title">Promo Bundling</h2>
                        <p>
                            @if($bundleMaxDiscount)
                                Diskon hingga {{ number_format($bundleMaxDiscount, 0) }}%
                            @else
                                Promo bundle khusus hari ini
                            @endif
                        </p>
                        @if($bundleEndsAt)
                            <div class="flash-sale-countdown" data-ends-at="{{ \Illuminate\Support\Carbon::parse($bundleEndsAt)->toIso8601String() }}">
                                Berakhir dalam <span class="flash-sale-countdown__value">--:--:--</span>
                            </div>
                        @endif
                    </div>
                    <a href="{{ route('shop.bundle-sale') }}">Lihat semua</a>
                </div>
                <div class="flash-sale-carousel">
                    @foreach($bundleCards as $card)
                        <a href="{{ route('shop.bundle.show', $card['promotion_id']) }}" class="product-card flash-sale-card">
                            <div class="product-card__image">
                                @if($card['image'])
                                    <img src="{{ Storage::url($card['image']->path) }}"
                                         alt="{{ $card['name'] }}"
                                         loading="lazy">
                                @else
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Crect fill='%23f3f4f6' width='200' height='200'/%3E%3C/svg%3E"
                                         alt="No image">
                                @endif

                                <span class="product-card__badge product-card__badge--bundle">Bundle</span>
                            </div>
                            <div class="product-card__info">
                                <h3 class="product-card__name">{{ $card['name'] }}</h3>
                                <div class="product-card__price-original">
                                    Rp {{ number_format($card['original_total'], 0, ',', '.') }}
                                </div>
                                <p class="product-card__price">
                                    Rp {{ number_format($card['bundle_price'], 0, ',', '.') }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($secondProducts->count() > 0)
            <div class="product-grid">
                @foreach($secondProducts as $product)
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
        @endif

        {{-- Pagination intentionally hidden on mobile shop (fixed 20 items) --}}
    @else
        {{-- Empty State --}}
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <div>
                <h3 class="empty-state__title">Produk Tidak Ditemukan</h3>
                <p class="empty-state__text">Coba cari dengan kata kunci lain atau lihat kategori lainnya</p>
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

document.querySelectorAll('.flash-sale-countdown').forEach((countdownEl) => {
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
});

const heroCarousel = document.querySelector('[data-hero-carousel]');
if (heroCarousel) {
    const track = heroCarousel.querySelector('[data-hero-track]');
    const slides = Array.from(heroCarousel.querySelectorAll('.hero-banner__slide'));
    const dots = Array.from(heroCarousel.querySelectorAll('[data-hero-dot]'));
    let currentIndex = 0;
    let autoplayTimer = null;
    let startX = null;

    const goToSlide = (index) => {
        if (!track || slides.length === 0) {
            return;
        }

        currentIndex = (index + slides.length) % slides.length;
        track.style.transform = `translateX(-${currentIndex * 100}%)`;

        slides.forEach((slide, slideIndex) => {
            slide.classList.toggle('is-active', slideIndex === currentIndex);
        });
        dots.forEach((dot, dotIndex) => {
            dot.classList.toggle('active', dotIndex === currentIndex);
        });
    };

    const nextSlide = () => goToSlide(currentIndex + 1);

    const resetAutoplay = () => {
        if (autoplayTimer) {
            clearInterval(autoplayTimer);
        }
        autoplayTimer = setInterval(nextSlide, 5000);
    };

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            const index = Number(dot.dataset.heroDot || 0);
            goToSlide(index);
            resetAutoplay();
        });
    });

    const onPointerDown = (event) => {
        startX = event.clientX ?? event.touches?.[0]?.clientX ?? null;
    };

    const onPointerUp = (event) => {
        if (startX === null) {
            return;
        }

        const endX = event.clientX ?? event.changedTouches?.[0]?.clientX ?? startX;
        const diff = endX - startX;
        startX = null;

        if (Math.abs(diff) < 40) {
            return;
        }

        if (diff < 0) {
            nextSlide();
        } else {
            goToSlide(currentIndex - 1);
        }
        resetAutoplay();
    };

    heroCarousel.addEventListener('pointerdown', onPointerDown);
    heroCarousel.addEventListener('pointerup', onPointerUp);
    heroCarousel.addEventListener('touchstart', onPointerDown, { passive: true });
    heroCarousel.addEventListener('touchend', onPointerUp, { passive: true });

    resetAutoplay();
}
</script>
@endsection

@php
    $seoProducts = $products->getCollection();
    $seoItemList = $seoProducts->values()->map(function ($product, $index) {
        return [
            '@type' => 'ListItem',
            'position' => $index + 1,
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
    'name' => 'Produk Toko',
    'itemListElement' => $seoItemList,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
