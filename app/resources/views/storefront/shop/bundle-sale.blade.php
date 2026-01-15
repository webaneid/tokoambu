@extends('storefront.layouts.app-mobile')

@section('title', 'Bundle - ' . config('app.name'))
@section('seo_title', 'Bundle - ' . \App\Models\Setting::get('store_name', config('app.name')))
@section('seo_description', $bundleMaxDiscount ? 'Promo bundle dengan diskon hingga ' . number_format($bundleMaxDiscount, 0) . '%.' : 'Promo bundle dengan harga spesial hari ini.')
@section('seo_url', route('shop.bundle-sale'))

@section('content')
<div class="shop-page flash-sale-page">
    <div class="flash-sale-page__header">
        <div>
            <h1>Bundle</h1>
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
    </div>

    @if($bundles->count() > 0)
        <div class="product-grid" data-next-url="{{ $bundles->nextPageUrl() }}">
            @foreach($bundles as $card)
                <a href="{{ route('shop.bundle.show', $card['promotion_id']) }}" class="product-card">
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

        @if($bundles->hasPages())
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
                <h3 class="empty-state__title">Promo Bundle Tidak Ditemukan</h3>
                <p class="empty-state__text">Belum ada bundling aktif saat ini</p>
            </div>
        </div>
    @endif
</div>

<script>
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
        console.error('Failed to load more bundles', error);
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
    $seoBundles = $bundles->getCollection();
    $seoItemList = $seoBundles->values()->map(function ($card, $index) use ($bundles) {
        $position = (($bundles->currentPage() - 1) * $bundles->perPage()) + $index + 1;
        return [
            '@type' => 'ListItem',
            'position' => $position,
            'url' => route('shop.bundle.show', $card['promotion_id']),
            'name' => $card['name'],
        ];
    });
@endphp

@push('seo')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => 'Promo Bundle',
    'itemListElement' => $seoItemList,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
