@extends('storefront.layouts.app-mobile')

@section('title', 'Lupa Password - Toko Ambu')

@section('content')
<div class="storefront-app">
    <main class="storefront-main">
        {{-- Header --}}
        <div class="auth-header">
            <a href="{{ route('customer.login') }}" class="back-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="auth-title">Lupa Password</h1>
            <div style="width: 24px;"></div>
        </div>

        {{-- Content --}}
        <div class="auth-content">
            {{-- Logo/Illustration --}}
            <div class="auth-illustration">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>

            {{-- Welcome Text --}}
            <div class="auth-welcome">
                <h2 class="auth-welcome-title">Lupa Password?</h2>
                <p class="auth-welcome-text">Masukkan email Anda dan kami akan mengirimkan tautan untuk mereset password</p>
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

            {{-- Forgot Password Form --}}
            <form action="{{ route('password.email') }}" method="POST" class="auth-form">
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

                {{-- Submit Button --}}
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    Kirim Tautan Reset
                </button>

                {{-- Back to Login --}}
                <div class="auth-links">
                    <a href="{{ route('customer.login') }}" class="auth-link">
                        Kembali ke login
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
