<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $storeName = \App\Models\Setting::get('store_name', config('app.name'));
        $faviconMediaId = \App\Models\Setting::get('favicon_media_id');
        $faviconMedia = $faviconMediaId ? \App\Models\Media::find($faviconMediaId) : null;
    @endphp

    <title>@yield('seo_title', $__env->yieldContent('title', $storeName))</title>
    @include('storefront.partials.seo')

    @if($faviconMedia)
        <link rel="icon" type="image/x-icon" href="{{ url($faviconMedia->url) }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ url($faviconMedia->url) }}">
    @endif

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Styles --}}
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @include('storefront.partials.theme-vars')
    @stack('styles')
    @stack('seo')
</head>
<body>
    <div class="storefront-app">
        {{-- Header --}}
        <header class="app-header">
            <div class="app-header__left">
                @php
                    $logoMediaId = \App\Models\Setting::get('logo_media_id');
                    $logoMedia = $logoMediaId ? \App\Models\Media::find($logoMediaId) : null;
                @endphp
                <a href="{{ route('shop.index') }}" class="app-header__logo" style="display: flex; align-items: center; justify-content: flex-start;">
                    @if($logoMedia)
                        <img src="{{ $logoMedia->url }}" alt="{{ $storeName }}" style="max-height: 80px; width: auto; object-fit: contain;">
                    @else
                        <span style="font-size: 1.2rem; font-weight: 600;">{{ $storeName }}</span>
                    @endif
                </a>
            </div>

            <div class="app-header__right">
                @php
                    $cartCount = app(\App\Services\CartService::class)->count();
                    $wishlistCount = auth('customer')->check()
                        ? \App\Models\Wishlist::where('customer_id', auth('customer')->id())->count()
                        : 0;
                @endphp
                {{-- Cart Button with Heroicon --}}
                <a href="{{ route('cart.index') }}" class="app-header__icon-btn app-header__icon-btn--circle">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                    @if($cartCount > 0)
                        <span class="app-header__badge" data-cart-count>{{ $cartCount }}</span>
                    @endif
                </a>

                {{-- User Profile with Dropdown --}}
                <div class="user-menu-wrapper">
                    @auth('customer')
                        @php
                            $unreadNotificationsCount = auth('customer')->user()->unreadNotifications()->count();
                        @endphp
                        <button type="button" class="app-header__profile" onclick="toggleUserMenu()">
                            @if(auth('customer')->user()->avatar)
                                <img src="{{ Storage::url(auth('customer')->user()->avatar) }}" alt="{{ auth('customer')->user()->name }}">
                            @else
                                @php
                                    $gravatarHash = md5(strtolower(trim(auth('customer')->user()->email)));
                                    $gravatarUrl = "https://www.gravatar.com/avatar/{$gravatarHash}?s=80&d=mp";
                                @endphp
                                <img src="{{ $gravatarUrl }}" alt="{{ auth('customer')->user()->name }}" loading="lazy">
                            @endif
                        </button>

                        {{-- Dropdown Menu --}}
                        <div class="user-dropdown" id="userDropdown">
                            <div class="user-dropdown__header">
                                <div class="user-dropdown__name">{{ auth('customer')->user()->name }}</div>
                                <div class="user-dropdown__email">{{ auth('customer')->user()->email }}</div>
                            </div>
                            <div class="user-dropdown__divider"></div>
                            <a href="{{ route('customer.profile') }}" class="user-dropdown__item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                <span>Profile</span>
                            </a>
                            <a href="{{ route('customer.dashboard') }}" class="user-dropdown__item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                </svg>
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('customer.notifications') }}" class="user-dropdown__item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9a6 6 0 0 0-12 0v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                </svg>
                                <span>Notifikasi</span>
                                @if($unreadNotificationsCount > 0)
                                    <span class="user-dropdown__badge">{{ $unreadNotificationsCount }}</span>
                                @endif
                            </a>
                            <div class="user-dropdown__divider"></div>
                            <form method="POST" action="{{ route('customer.logout') }}" class="user-dropdown__form">
                                @csrf
                                <button type="submit" class="user-dropdown__item user-dropdown__item--danger">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                    </svg>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('customer.login') }}" class="app-header__profile">
                            <div class="app-header__profile-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        {{-- Search Bar (Always visible below header) --}}
        <div class="search-bar">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input type="text"
                   placeholder="Search"
                   value="{{ request('q') }}"
                   onkeyup="handleSearch(event)">
            <button type="button" class="search-bar__btn" onclick="performSearch()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </button>
        </div>

        {{-- Main Content --}}
        <main class="storefront-main">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="storefront-footer">
            <div class="storefront-footer__content">
                {{-- Store Info --}}
                <div class="storefront-footer__info">
                    <h3 class="storefront-footer__title">{{ \App\Models\Setting::get('store_name', config('app.name')) }}</h3>

                    @if(\App\Models\Setting::get('store_address'))
                        <div class="storefront-footer__contact">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="storefront-footer__icon">
                                <path fill-rule="evenodd" d="m9.69 18.933.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 0 0 .281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 1 0 3 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 0 0 2.273 1.765 11.842 11.842 0 0 0 .976.544l.062.029.018.008.006.003ZM10 11.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ \App\Models\Setting::get('store_address') }}</span>
                        </div>
                    @endif

                    @if(\App\Models\Setting::get('store_phone'))
                        <div class="storefront-footer__contact">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="storefront-footer__icon">
                                <path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.148a1.5 1.5 0 0 1 1.465 1.175l.716 3.223a1.5 1.5 0 0 1-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 0 0 6.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 0 1 1.767-1.052l3.223.716A1.5 1.5 0 0 1 18 15.352V16.5a1.5 1.5 0 0 1-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 0 1 2.43 8.326 13.019 13.019 0 0 1 2 5V3.5Z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ \App\Models\Setting::get('store_phone') }}</span>
                        </div>
                    @endif
                </div>

                {{-- Footer Navigation Menu --}}
                @php
                    $footerMenuItems = \App\Models\FooterMenuItem::with('page')->active()->ordered()->get();
                @endphp
                @if($footerMenuItems->count() > 0)
                <nav class="storefront-footer__nav">
                    @foreach($footerMenuItems as $item)
                        <a href="{{ $item->getUrl() }}" class="storefront-footer__nav-link">{{ $item->label }}</a>
                    @endforeach
                </nav>
                @endif
                <div class="storefront-footer__social">
                    @if(\App\Models\Setting::get('whatsapp_number'))
                        <a href="https://wa.me/{{ \App\Models\Setting::get('whatsapp_number') }}" target="_blank" class="storefront-footer__social-link">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                        </a>
                    @endif
                    @if(\App\Models\Setting::get('instagram_handle'))
                        <a href="https://instagram.com/{{ \App\Models\Setting::get('instagram_handle') }}" target="_blank" class="storefront-footer__social-link">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                    @endif
                </div>
                <div class="storefront-footer__copyright">
                    <p>&copy; {{ date('Y') }} {{ \App\Models\Setting::get('store_name', config('app.name')) }}. All rights reserved.</p>
                </div>
            </div>
        </footer>

        {{-- Bottom Navigation --}}
        <nav class="bottom-nav">
            <div class="bottom-nav__container">
                {{-- Orders --}}
                <a href="{{ route('customer.orders') }}" class="nav-item {{ request()->routeIs('customer.orders') || request()->routeIs('customer.order.show') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                </a>

                {{-- Dashboard --}}
                <a href="{{ route('customer.dashboard') }}" class="nav-item {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                </a>

                {{-- Home (Center Big Button) --}}
                <a href="{{ route('shop.index') }}" class="nav-item nav-item--center {{ request()->routeIs('shop.index') && !request()->has('category') && !request()->has('search') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                </a>

                {{-- Wishlist --}}
                <a href="{{ auth('customer')->check() ? route('customer.wishlist.index') : route('customer.login') }}" class="nav-item {{ request()->routeIs('customer.wishlist.index') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 6.522a4.75 4.75 0 0 0-8.232-2.448l-.77 1.026-.77-1.026a4.75 4.75 0 0 0-8.232 2.448c-.217 1.47.214 2.975 1.189 4.085l7.813 8.952a.75.75 0 0 0 1.132 0l7.813-8.952a6.007 6.007 0 0 0 1.219-4.085Z" />
                    </svg>
                    @if($wishlistCount > 0)
                        <span class="nav-item__badge" data-wishlist-count>{{ $wishlistCount }}</span>
                    @endif
                </a>

                {{-- Profile --}}
                <a href="{{ route('customer.profile') }}" class="nav-item {{ request()->routeIs('customer.profile') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </a>
            </div>
        </nav>
    </div>

    <script>
        function handleSearch(event) {
            if (event.key === 'Enter') {
                performSearch();
            }
        }

        function performSearch() {
            const input = document.querySelector('.search-bar input');
            const query = input.value;
            if (query.trim()) {
                window.location.href = `{{ route('shop.search') }}?q=${encodeURIComponent(query)}`;
            }
        }

        // Toggle User Dropdown Menu
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const profileBtn = document.querySelector('.app-header__profile');
            
            if (dropdown && profileBtn) {
                if (!profileBtn.contains(event.target) && !dropdown.contains(event.target)) {
                    dropdown.classList.remove('show');
                }
            }
        });
    </script>

    {{-- Suppress "No checkout popup config found" error --}}
    <script>
        // Global error handler to suppress specific Alpine/popup errors
        window.addEventListener('unhandledrejection', function(event) {
            if (event.reason && event.reason.message &&
                event.reason.message.includes('No checkout popup config found')) {
                console.warn('Suppressed popup config error (legacy code)');
                event.preventDefault();
            }
        });
    </script>

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

    {{-- Cart Modal Functions --}}
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

        // Close modal when clicking backdrop
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
