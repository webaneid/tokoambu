@extends('storefront.layouts.app-mobile')

@section('title', 'Daftar Akun - Toko Ambu')

@section('content')
<div class="storefront-app">
    <main class="storefront-main">
        {{-- Header --}}
        <div class="auth-header">
            <a href="{{ route('shop.index') }}" class="back-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="auth-title">Daftar Akun</h1>
            <div style="width: 24px;"></div>
        </div>

        {{-- Content --}}
        <div class="auth-content">
            {{-- Logo/Illustration --}}
            <div class="auth-illustration">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <line x1="19" y1="8" x2="19" y2="14"></line>
                    <line x1="22" y1="11" x2="16" y2="11"></line>
                </svg>
            </div>

            {{-- Welcome Text --}}
            <div class="auth-welcome">
                <h2 class="auth-welcome-title">Buat Akun Baru</h2>
                <p class="auth-welcome-text">Daftar untuk mulai berbelanja di Toko Ambu</p>
            </div>

            {{-- Status Messages --}}
            @if ($errors->any())
                <div class="auth-alert auth-alert-error">
                    @foreach ($errors->all() as $error)
                        <p class="auth-alert-text">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if (session('status'))
                <div class="auth-alert auth-alert-success">
                    <p class="auth-alert-text">{{ session('status') }}</p>
                </div>
            @endif

            {{-- Register Form --}}
            <form action="{{ route('customer.register') }}" method="POST" class="auth-form">
                @csrf

                {{-- Name Field --}}
                <div class="form-group">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input
                        type="text"
                        class="form-input @error('name') form-input-error @enderror"
                        id="name"
                        name="name"
                        placeholder="Masukkan nama Anda"
                        value="{{ old('name') }}"
                        required
                        autofocus
                    >
                    @error('name')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Email Field --}}
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input
                        type="email"
                        class="form-input @error('email') form-input-error @enderror"
                        id="email"
                        name="email"
                        placeholder="nama@email.com"
                        value="{{ old('email') }}"
                        required
                    >
                    @error('email')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Phone Field --}}
                <div class="form-group">
                    <label for="phone" class="form-label">Nomor Telepon</label>
                    <input
                        type="tel"
                        class="form-input @error('phone') form-input-error @enderror"
                        id="phone"
                        name="phone"
                        placeholder="08xxxxxxxxxx"
                        value="{{ old('phone') }}"
                        required
                    >
                    <small class="form-hint">Contoh: 081234567890</small>
                    @error('phone')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Password Field --}}
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input
                        type="password"
                        class="form-input @error('password') form-input-error @enderror"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                    >
                    <small class="form-hint">Min. 8 karakter, kombinasi huruf, angka, dan simbol</small>
                    @error('password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Password Confirmation Field --}}
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                    <input
                        type="password"
                        class="form-input @error('password_confirmation') form-input-error @enderror"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Ulangi password"
                        required
                    >
                    @error('password_confirmation')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    Daftar Sekarang
                </button>

                {{-- Login Link --}}
                <div class="auth-footer">
                    <span class="auth-footer-text">Sudah punya akun?</span>
                    <a href="{{ route('customer.login') }}" class="auth-footer-link">
                        Masuk di sini
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection
