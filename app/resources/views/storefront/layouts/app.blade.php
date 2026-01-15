<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    @php
        $storeName = \App\Models\Setting::get('store_name', 'Toko Ambu');
        $faviconMediaId = \App\Models\Setting::get('favicon_media_id');
        $faviconMedia = $faviconMediaId ? \App\Models\Media::find($faviconMediaId) : null;
    @endphp

    <title>@yield('seo_title', $__env->yieldContent('title', $storeName))</title>
    @include('storefront.partials.seo')

    @if($faviconMedia)
        <link rel="icon" type="image/x-icon" href="{{ url($faviconMedia->url) }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ url($faviconMedia->url) }}">
    @endif

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Storefront CSS -->
    <link rel="stylesheet" href="{{ asset('css/storefront.css') }}">

    @stack('styles')
    @stack('seo')
</head>
<body>
    <!-- Header/Navigation -->
    <header class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
        <div class="container">
            @php
                $storeName = \App\Models\Setting::get('store_name', 'Toko Ambu');
                $logoMediaId = \App\Models\Setting::get('logo_media_id');
                $logoMedia = $logoMediaId ? \App\Models\Media::find($logoMediaId) : null;
            @endphp
            <a class="navbar-brand" href="/" style="max-width: 30%;">
                @if($logoMedia)
                    <img src="{{ $logoMedia->url }}" alt="{{ $storeName }}" class="img-fluid" style="max-height: 50px; width: auto;">
                @else
                    <span class="fw-bold">{{ $storeName }}</span>
                @endif
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    @auth('customer')
                        <a class="nav-link" href="{{ route('customer.dashboard') }}">Akun Saya</a>
                        <form action="{{ route('customer.logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link" style="text-decoration: none; cursor: pointer;">
                                Keluar
                            </button>
                        </form>
                    @else
                        <a class="nav-link" href="{{ route('customer.login') }}">Masuk</a>
                        <a class="nav-link" href="{{ route('customer.register') }}">Daftar</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    @yield('content')

    <!-- Footer -->
    <footer class="bg-light border-top mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    @php
                        $storeName = \App\Models\Setting::get('store_name', 'Toko Ambu');
                    @endphp
                    <h6 class="fw-bold mb-3">{{ $storeName }}</h6>
                    <p class="text-muted small">
                        Platform belanja online terpercaya untuk kebutuhan Anda.
                    </p>
                </div>
                <div class="col-md-4 mb-4">
                    <h6 class="fw-bold mb-3">Informasi</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#" class="text-muted text-decoration-none">Tentang Kami</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Kebijakan Privasi</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h6 class="fw-bold mb-3">Hubungi Kami</h6>
                    <p class="text-muted small">
                        Email: support@tokoambu.com<br>
                        Phone: 081234567890
                    </p>
                </div>
            </div>
            <hr>
            <div class="text-center text-muted small">
                @php
                    $storeName = \App\Models\Setting::get('store_name', 'Toko Ambu');
                @endphp
                <p class="mb-0">&copy; {{ date('Y') }} {{ $storeName }}. Semua hak dilindungi.</p>
            </div>
        </div>
    </footer>

    {{-- Cart Success Modal --}}
    <div id="cartSuccessModal" class="cart-modal" style="display: none;">
        <div class="cart-modal__backdrop"></div>
        <div class="cart-modal__content">
            <div class="cart-modal__icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                </svg>
            </div>
            <h3 class="cart-modal__title" id="cartModalTitle">Produk berhasil ditambahkan!</h3>
            <p class="cart-modal__message" id="cartModalMessage">Produk berhasil ditambahkan ke keranjang</p>
            <div class="cart-modal__actions">
                <button type="button" class="cart-modal__btn cart-modal__btn--secondary" onclick="closeCartModal()">
                    Lanjut Belanja
                </button>
                <a href="{{ route('cart.index') }}" class="cart-modal__btn cart-modal__btn--primary">
                    Buka Keranjang
                </a>
            </div>
        </div>
    </div>

    {{-- Wishlist Success Modal --}}
    <div id="wishlistSuccessModal" class="cart-modal" style="display: none;">
        <div class="cart-modal__backdrop"></div>
        <div class="cart-modal__content">
            <div class="cart-modal__icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                </svg>
            </div>
            <h3 class="cart-modal__title" id="wishlistModalTitle">Wishlist Diperbarui!</h3>
            <p class="cart-modal__message" id="wishlistModalMessage">Produk ditambahkan ke wishlist</p>
            <div class="cart-modal__actions">
                <button type="button" class="cart-modal__btn cart-modal__btn--secondary" onclick="closeWishlistModal()">
                    Lanjut Belanja
                </button>
                <a href="{{ route('customer.wishlist.index') }}" class="cart-modal__btn cart-modal__btn--primary">
                    Lihat Wishlist
                </a>
            </div>
        </div>
    </div>

    {{-- Share Modal --}}
    <div id="shareModal" class="cart-modal" style="display: none;">
        <div class="cart-modal__backdrop"></div>
        <div class="cart-modal__content share-modal__content">
            <button type="button" class="share-modal__close" aria-label="Tutup" onclick="closeShareModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6m0 12L6 6" />
                </svg>
            </button>
            <div class="cart-modal__icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.75 3.75a3 3 0 1 1-.192 5.995l-5.289 3.023a3 3 0 1 1-.03 2.464l5.347 3.056a3 3 0 1 1-.72 1.31l-5.403-3.088a3 3 0 1 1 .022-3.406l5.345-3.058A3 3 0 0 1 15.75 3.75Z" />
                </svg>
            </div>
            <h3 class="cart-modal__title">Bagikan Produk</h3>
            <p class="cart-modal__message">Pilih platform favoritmu untuk berbagi.</p>
            <div class="share-modal__grid">
                <button type="button" class="share-option share-option--whatsapp" data-share-target="whatsapp">
                    <span class="share-option__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M16.6 14.2c-.2-.1-1.1-.6-1.3-.7-.2-.1-.3-.1-.5.1-.1.2-.5.7-.6.8-.1.1-.2.2-.4.1-.2-.1-.9-.3-1.7-1.1-.6-.5-1-1.1-1.1-1.3-.1-.2 0-.3.1-.4.1-.1.2-.2.3-.4.1-.1.1-.2.2-.3.1-.1 0-.3 0-.4s-.5-1.2-.7-1.6c-.2-.4-.4-.4-.5-.4h-.4c-.1 0-.3 0-.5.2-.2.2-.6.6-.6 1.5s.6 1.7.7 1.9c.1.1 1.2 1.8 2.9 2.5.4.2.7.3 1 .4.4.1.8.1 1.1.1.3-.1.9-.4 1-.7.1-.3.1-.6.1-.7 0-.1-.2-.1-.3-.2z" />
                            <path d="M12 2a9.94 9.94 0 0 0-8.5 15.1L2 22l5-1.4A9.94 9.94 0 1 0 12 2zm0 18.2c-1.5 0-2.9-.4-4.2-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2z" />
                        </svg>
                    </span>
                    <span class="share-option__label">WhatsApp</span>
                </button>
                <button type="button" class="share-option" data-share-target="facebook">
                    <span class="share-option__icon share-option__icon--facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                            <path fill="#1877F2" d="M12 2.25a9.75 9.75 0 0 0-1.5 19.392v-6.876H8.25V12h2.25V9.75c0-2.225 1.326-3.451 3.357-3.451.973 0 1.989.173 1.989.173v2.187h-1.121c-1.104 0-1.448.685-1.448 1.387V12h2.465l-.394 2.766h-2.071v6.876A9.75 9.75 0 0 0 12 2.25Z" />
                        </svg>
                    </span>
                    <span class="share-option__label">Facebook</span>
                </button>
                <button type="button" class="share-option" data-share-target="linkedin">
                    <span class="share-option__icon share-option__icon--linkedin">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                            <path fill="#0A66C2" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Z" />
                            <path fill="#fff" d="M8.25 17.25H6v-7.5h2.25v7.5Zm-1.125-8.571a1.286 1.286 0 1 1 0-2.571 1.286 1.286 0 0 1 0 2.571Zm10.125 8.571H15V13.5c0-.9-.375-1.5-1.2-1.5-.63 0-1.012.42-1.183.828-.06.132-.075.315-.075.498v3.924H10.5v-7.5h2.043v1.005c.297-.459.84-1.11 2.042-1.11 1.5 0 2.667.981 2.667 3.093v4.512Z" />
                        </svg>
                    </span>
                    <span class="share-option__label">LinkedIn</span>
                </button>
                <button type="button" class="share-option" data-share-target="x">
                    <span class="share-option__icon share-option__icon--x">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M18.244 2.25h3.308l-7.225 8.26 8.5 11.24h-6.65l-5.21-6.82-5.97 6.82H1.69l7.72-8.83L1.25 2.25h6.82l4.72 6.24 5.45-6.24Zm-1.16 17.28h1.83L7.18 4.14H5.2l11.88 15.39Z" />
                        </svg>
                    </span>
                    <span class="share-option__label">X (Twitter)</span>
                </button>
                <button type="button" class="share-option" data-share-target="copy">
                    <span class="share-option__icon share-option__icon--copy">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 8.25V6A2.25 2.25 0 0 1 10.5 3.75h7.5A2.25 2.25 0 0 1 20.25 6v7.5A2.25 2.25 0 0 1 18 15.75h-2.25M6 8.25H4.5A2.25 2.25 0 0 0 2.25 10.5v7.5A2.25 2.25 0 0 0 4.5 20.25h7.5A2.25 2.25 0 0 0 14.25 18V16.5" />
                        </svg>
                    </span>
                    <span class="share-option__label">Salin Link</span>
                </button>
            </div>
            <p class="share-modal__status" id="shareModalStatus" style="display: none;"></p>
        </div>
    </div>

    <style>
        .share-modal__content {
            position: relative;
            padding-top: 3rem;
        }

        .share-modal__close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            border: none;
            background: transparent;
            width: 32px;
            height: 32px;
            cursor: pointer;
            color: #6b7280;
        }

        .share-modal__grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.75rem;
            width: 100%;
            margin-top: 1rem;
        }

        .share-option {
            border: 1px solid #e5e7eb;
            border-radius: 0.9rem;
            padding: 0.85rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            background: #fff;
            cursor: pointer;
            transition: border-color 0.2s ease, transform 0.2s ease;
            font-weight: 600;
            color: #111827;
        }

        .share-option:hover {
            border-color: #111827;
            transform: translateY(-2px);
        }

        .share-option__icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
        }

        .share-option__icon svg {
            width: 32px;
            height: 32px;
        }

        .share-option__icon--x svg {
            width: 24px;
            height: 24px;
        }

        .share-option__label {
            font-size: 0.9rem;
        }

        .share-modal__status {
            margin-top: 0.75rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #047857;
        }

        .share-option--whatsapp {
            color: #15803d;
            border-color: #bbf7d0;
            background: #ecfdf5;
        }

        .share-option--whatsapp .share-option__icon {
            background: #dcfce7;
        }
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        window.showCartSuccessModal = function(message = 'Produk berhasil ditambahkan ke keranjang', isPreorder = false) {
            const modal = document.getElementById('cartSuccessModal');
            const messageEl = document.getElementById('cartModalMessage');
            const titleEl = document.getElementById('cartModalTitle');

            if (isPreorder) {
                titleEl.textContent = 'Preorder Berhasil!';
            } else {
                titleEl.textContent = 'Berhasil Ditambahkan!';
            }

            messageEl.textContent = message;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        };

        window.closeCartModal = function() {
            const modal = document.getElementById('cartSuccessModal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        };

        window.showWishlistSuccessModal = function(message = 'Produk ditambahkan ke wishlist') {
            const modal = document.getElementById('wishlistSuccessModal');
            const messageEl = document.getElementById('wishlistModalMessage');
            const titleEl = document.getElementById('wishlistModalTitle');

            titleEl.textContent = 'Wishlist Diperbarui!';
            messageEl.textContent = message;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        };

        window.closeWishlistModal = function() {
            const modal = document.getElementById('wishlistSuccessModal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        };

        (function() {
            const modal = document.getElementById('shareModal');
            if (!modal) return;

            const statusEl = document.getElementById('shareModalStatus');
            let currentPayload = { url: '', text: '', title: '' };

            const shareTargets = {
                whatsapp: (payload) => {
                    const message = `${payload.text || ''} ${payload.url || ''}`.trim();
                    return `https://wa.me/?text=${encodeURIComponent(message)}`;
                },
                facebook: (payload) => `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(payload.url || '')}`,
                linkedin: (payload) => `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(payload.url || '')}`,
                x: (payload) => {
                    const text = payload.text || payload.title || '';
                    return `https://twitter.com/intent/tweet?url=${encodeURIComponent(payload.url || '')}&text=${encodeURIComponent(text)}`;
                }
            };

            window.showShareModal = function(payload = {}) {
                currentPayload = {
                    url: payload.url || window.location.href,
                    text: payload.text || document.title,
                    title: payload.title || document.title
                };

                if (statusEl) {
                    statusEl.style.display = 'none';
                    statusEl.textContent = '';
                }

                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            };

            window.closeShareModal = function() {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            };

            modal.querySelectorAll('[data-share-target]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const target = btn.dataset.shareTarget;

                    if (target === 'copy') {
                        if (navigator.clipboard && currentPayload.url) {
                            navigator.clipboard.writeText(currentPayload.url).then(() => {
                                if (statusEl) {
                                    statusEl.textContent = 'Link produk berhasil disalin!';
                                    statusEl.style.display = 'block';
                                }
                            }).catch(() => {
                                alert('Gagal menyalin link produk.');
                            });
                        } else {
                            alert('Clipboard tidak tersedia di perangkat ini.');
                        }
                        return;
                    }

                    const builder = shareTargets[target];
                    if (builder) {
                        const url = builder(currentPayload);
                        window.open(url, '_blank', 'noopener');
                    }
                });
            });
        })();

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('cart-modal__backdrop')) {
                closeCartModal();
                closeWishlistModal();
                if (typeof closeShareModal === 'function') {
                    closeShareModal();
                }
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
