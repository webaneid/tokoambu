@extends('storefront.layouts.app-mobile')

@section('title', 'Masuk - Toko Ambu')

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
            <h1 class="auth-title">Masuk</h1>
            <div style="width: 24px;"></div>
        </div>

        {{-- Content --}}
        <div class="auth-content">
            {{-- Logo/Illustration --}}
            <div class="auth-illustration">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>

            {{-- Welcome Text --}}
            <div class="auth-welcome">
                <h2 class="auth-welcome-title">Masuk ke Akun</h2>
                <p class="auth-welcome-text">Masukkan email dan password untuk melanjutkan</p>
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

            {{-- Login Form --}}
            <form action="{{ route('customer.login') }}" method="POST" class="auth-form">
                @csrf

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
                        autofocus
                    >
                    @error('email')
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
                    @error('password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="form-checkbox">
                    <input
                        type="checkbox"
                        class="checkbox-input"
                        name="remember"
                        id="remember"
                        value="1"
                        {{ old('remember') ? 'checked' : '' }}
                    >
                    <label class="checkbox-label" for="remember">
                        Ingat saya di perangkat ini
                    </label>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    Masuk
                </button>

                {{-- Forgot Password --}}
                <div class="auth-links">
                    <a href="{{ route('password.request') }}" class="auth-link">
                        Lupa password?
                    </a>
                </div>

                {{-- Register Link --}}
                <div class="auth-footer">
                    <span class="auth-footer-text">Belum punya akun?</span>
                    <a href="{{ route('customer.register') }}" class="auth-footer-link">
                        Daftar di sini
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection
