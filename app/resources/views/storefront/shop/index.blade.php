@extends('storefront.layouts.app')

@section('title', 'Toko Belanja - Toko Ambu')
@section('seo_title', 'Shop - ' . \App\Models\Setting::get('store_name', 'Toko Ambu'))
@section('seo_description', 'Jelajahi produk pilihan, promo menarik, dan rekomendasi terbaik di toko kami.')
@section('seo_url', route('shop.index'))

@section('content')
<div class="storefront-app">
    <main class="main-content">
        <!-- Hero Section -->
        <section class="shop-hero bg-primary py-5 text-white">
            <div class="container">
                <h1 class="display-5 fw-bold mb-2">Belanja Produk</h1>
                <p class="lead mb-0">Jelajahi koleksi lengkap kami dengan harga terbaik</p>
            </div>
        </section>

        <!-- Shop Section -->
        <section class="shop-section py-5">
            <div class="container">
        <!-- Filters & Search -->
                <div class="row mb-4 g-3">
                    <!-- Search Form -->
                    <div class="col-12 col-md-6">
                        <form action="{{ route('shop.index') }}" method="GET" class="d-flex gap-2">
                            <input 
                                type="text" 
                                name="q" 
                                class="form-control" 
                                placeholder="Cari nama, SKU, atau deskripsi..."
                                value="{{ $searchQuery }}"
                            >
                            <button type="submit" class="btn btn-primary" title="Cari produk">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                            </button>
                        </form>
                    </div>

                    <!-- Category Filter -->
                    <div class="col-12 col-md-3">
                        <form action="{{ route('shop.index') }}" method="GET" id="filterForm" class="d-flex gap-2">
                            <input type="hidden" name="q" value="{{ $searchQuery }}">
                            <input type="hidden" name="sort" value="{{ $selectedSort }}">
                            
                            <select name="category" class="form-select" onchange="document.getElementById('filterForm').submit();">
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected($selectedCategory == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    <!-- Sorting -->
                    <div class="col-12 col-md-3">
                        <form action="{{ route('shop.index') }}" method="GET" id="sortForm" class="d-flex gap-2">
                            <input type="hidden" name="q" value="{{ $searchQuery }}">
                            <input type="hidden" name="category" value="{{ $selectedCategory }}">
                            
                            <select name="sort" class="form-select" onchange="document.getElementById('sortForm').submit();">
                                <option value="newest" @selected($selectedSort === 'newest')>Terbaru</option>
                                <option value="price_low" @selected($selectedSort === 'price_low')>Harga: Terendah</option>
                                <option value="price_high" @selected($selectedSort === 'price_high')>Harga: Tertinggi</option>
                                <option value="popular" @selected($selectedSort === 'popular')>Paling Populer</option>
                            </select>
                        </form>
                    </div>

                    <!-- Clear Filters Button -->
                    @if ($searchQuery || $selectedCategory || $selectedSort !== 'newest')
                        <div class="col-12">
                            <a href="{{ route('shop.index') }}" class="btn btn-sm btn-outline-secondary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                    <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                    <path d="M21 3v5h-5"></path>
                                    <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                    <path d="M3 21v-5h5"></path>
                                </svg>
                                Hapus Filter
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Products Grid -->
                @if ($products->count() > 0)
                    <div class="row g-4 mb-5">
                        @foreach ($products as $product)
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                <!-- Product Card -->
                                <div class="product-card card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                                    <!-- Product Image -->
                                    <div class="position-relative overflow-hidden" style="height: 200px; background: #f0f0f0;">
                                        @if ($product->featuredMedia)
                                            <img
                                                src="{{ Storage::url($product->featuredMedia->path) }}"
                                                alt="{{ $product->name }}"
                                                class="img-fluid w-100 h-100 object-fit-cover"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" opacity="0.5">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                    <polyline points="21 15 16 10 5 21"></polyline>
                                                </svg>
                                            </div>
                                        @endif

                                        <!-- Stock/Preorder Badge -->
                                        @php
                                            $hasActivePreorder = $product->preorderPeriods->count() > 0;
                                            $totalStock = $product->inventoryBalances->sum('qty_on_hand');
                                            $reservedStock = $reservedQuantities->get($product->id, 0);
                                            $availableStock = max(0, $totalStock - $reservedStock);
                                        @endphp

                                        @if ($hasActivePreorder)
                                            <span class="badge bg-info position-absolute bottom-0 start-0 m-2">Preorder</span>
                                        @elseif ($availableStock <= 0)
                                            <span class="badge bg-danger position-absolute bottom-0 start-0 m-2">Stok Habis</span>
                                        @elseif ($availableStock < 5)
                                            <span class="badge bg-warning text-dark position-absolute bottom-0 start-0 m-2">Stok: {{ $availableStock }}</span>
                                        @endif
                                    </div>

                                    <!-- Product Info -->
                                    <div class="card-body d-flex flex-column">
                                        <!-- Product Name -->
                                        <h5 class="card-title fw-bold mb-3 line-clamp-2" style="font-size: 0.95rem;">
                                            <a href="{{ route('shop.show', $product->slug) }}" class="text-decoration-none text-dark">
                                                {{ $product->name }}
                                            </a>
                                        </h5>

                                        <!-- Price -->
                                        <div class="mb-3 mt-auto">
                                            @if ($product->has_variants && $product->variants->count() > 0)
                                                @php
                                                    $minPrice = $product->variants->min('selling_price');
                                                    $maxPrice = $product->variants->max('selling_price');
                                                @endphp
                                                <div class="h6 text-primary fw-bold mb-0">
                                                    @if ($minPrice == $maxPrice)
                                                        Rp {{ number_format($minPrice, 0, ',', '.') }}
                                                    @else
                                                        Rp {{ number_format($minPrice, 0, ',', '.') }} - Rp {{ number_format($maxPrice, 0, ',', '.') }}
                                                    @endif
                                                </div>
                                                <small class="text-muted">{{ $product->variants->count() }} Variasi</small>
                                            @else
                                                <div class="h6 text-primary fw-bold mb-0">
                                                    Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Add to Cart Button -->
                                        @if ($product->has_variants && $product->variants->count() > 0)
                                            <a
                                                href="{{ route('shop.show', $product->slug) }}"
                                                class="btn btn-primary btn-sm w-100"
                                            >
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                                Pilih Variasi
                                            </a>
                                        @else
                                            @php
                                                $totalStock = $product->inventoryBalances->sum('qty_on_hand');
                                                $reservedStock = $reservedQuantities->get($product->id, 0);
                                                $availableStock = max(0, $totalStock - $reservedStock);
                                            @endphp
                                            <button
                                                class="btn btn-primary btn-sm w-100 add-to-cart-btn"
                                                type="button"
                                                data-product-id="{{ $product->id }}"
                                                data-product-name="{{ $product->name }}"
                                                data-product-price="{{ $product->selling_price }}"
                                                @if ($availableStock <= 0) disabled @endif
                                            >
                                                @if ($availableStock > 0)
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                                        <circle cx="9" cy="21" r="1"></circle>
                                                        <circle cx="20" cy="21" r="1"></circle>
                                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                                    </svg>
                                                    Tambah ke Keranjang
                                                @else
                                                    Stok Habis
                                                @endif
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Pagination" class="d-flex justify-content-center">
                        {{ $products->links('pagination::bootstrap-5') }}
                    </nav>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="text-muted mb-3">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <h5 class="text-muted mt-3">Tidak ada produk ditemukan</h5>
                        <p class="text-muted small mb-4">
                            @if ($searchQuery)
                                Tidak ada produk yang cocok dengan "<strong>{{ $searchQuery }}</strong>"
                            @elseif ($selectedCategory)
                                Tidak ada produk di kategori ini
                            @else
                                Coba cari dengan kata kunci yang berbeda
                            @endif
                        </p>
                        <a href="{{ route('shop.index') }}" class="btn btn-primary btn-sm">
                            Lihat Semua Produk
                        </a>
                    </div>
                @endif
            </div>
        </section>
    </main>
</div>

<style scoped>
.shop-hero {
    background: linear-gradient(135deg, #f17b0d 0%, #f17b0d 100%);
}

.product-card {
    transition: transform 0.3s, box-shadow 0.3s;
    cursor: pointer;
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
}

.btn-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.object-fit-cover {
    object-fit: cover;
}

.bg-primary {
    background-color: #f17b0d !important;
}

.text-primary {
    color: #f17b0d !important;
}

.btn-primary {
    background-color: #f17b0d;
    border-color: #f17b0d;
}

.btn-primary:hover {
    background-color: #d96409;
    border-color: #d96409;
}

@media (max-width: 576px) {
    .product-card {
        margin-bottom: 1rem;
    }
    
    .shop-hero {
        padding: 2rem 0 !important;
    }
    
    .shop-hero h1 {
        font-size: 1.75rem !important;
    }
}
</style>

@push('scripts')
<script>
document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        
        const productId = this.dataset.productId;
        const productName = this.dataset.productName;
        const btn = this;
        const originalText = btn.innerHTML;
        
        // Disable button & show loading
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menambahkan...';
        
        fetch('{{ route("cart.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1,
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show toast/notification
                const toast = document.createElement('div');
                toast.className = 'alert alert-success position-fixed bottom-3 end-3 me-3 mb-3';
                toast.style.zIndex = '9999';
                toast.innerHTML = `
                    <strong>Berhasil!</strong> ${productName} ditambahkan ke keranjang.
                    <a href="{{ route('cart.index') }}" class="ms-2 text-decoration-none">Lihat Keranjang</a>
                `;
                document.body.appendChild(toast);
                
                // Update cart count if you have one
                const cartCount = document.querySelector('[data-cart-count]');
                if (cartCount) {
                    cartCount.textContent = data.count;
                }
                
                // Auto hide toast
                setTimeout(() => toast.remove(), 4000);
            } else {
                alert(data.message || 'Gagal menambahkan ke keranjang');
            }
            
            // Re-enable button
            btn.disabled = false;
            btn.innerHTML = originalText;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menambahkan ke keranjang');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });
});
</script>
@endpush
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
