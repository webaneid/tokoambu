@extends('storefront.layouts.app')

@section('title', 'Reset Password - Toko Ambu')

@section('content')
<div class="storefront-app">
    <main class="main-content">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-6">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <h1 class="h2 fw-bold mb-2">Reset Password</h1>
                        <p class="text-muted">Buat password baru untuk akun Anda</p>
                    </div>

                    <!-- Reset Password Form -->
                    <form action="{{ route('password.store') }}" method="POST" class="needs-validation" novalidate>
                        @csrf

                        <!-- Token Field (Hidden) -->
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <!-- Status Messages -->
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Email Field -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-500">Email</label>
                            <input 
                                type="email"
                                class="form-control form-control-lg @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                placeholder="nama@email.com"
                                value="{{ old('email', $request->email) }}"
                                required
                                autofocus
                            >
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-500">Password Baru</label>
                            <input 
                                type="password"
                                class="form-control form-control-lg @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                placeholder="Masukkan password baru"
                                required
                            >
                            <small class="text-muted d-block mt-2">
                                Minimal 8 karakter, harus kombinasi huruf, angka, dan simbol
                            </small>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Confirmation Field -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-500">Konfirmasi Password</label>
                            <input 
                                type="password"
                                class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Ulangi password baru"
                                required
                            >
                            @error('password_confirmation')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                            Reset Password
                        </button>

                        <!-- Divider -->
                        <div class="text-center mb-4">
                            <small class="text-muted">Atau</small>
                        </div>

                        <!-- Back Link -->
                        <p class="text-center mb-0">
                            <a href="{{ route('customer.login') }}" class="text-decoration-none fw-500">
                                Kembali ke login
                            </a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<style scoped>
.form-control-lg {
    padding: 0.875rem 1rem;
    font-size: 1rem;
    border-radius: 8px;
}

.form-label {
    margin-bottom: 0.5rem;
    color: #333;
}

.fw-500 {
    font-weight: 500;
}

.is-invalid {
    border-color: #d00086;
}

.invalid-feedback {
    color: #d00086;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.alert {
    border-radius: 8px;
}
</style>
@endsection
