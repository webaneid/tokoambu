@extends('storefront.layouts.app')

@section('title', 'Verifikasi Email - Toko Ambu')

@section('content')
<div class="storefront-app">
    <main class="main-content">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-6">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <div class="mb-4">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <h1 class="h2 fw-bold mb-2">Verifikasi Email</h1>
                        <p class="text-muted">
                            Email Anda sedang diverifikasi. Silakan tunggu sebentar...
                        </p>
                    </div>

                    <!-- Status Message -->
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <div>
                                    <strong>Berhasil!</strong> Email Anda telah diverifikasi.
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <p class="text-center mb-3">
                            Akun Anda sekarang aktif dan siap untuk berbelanja.
                        </p>

                        <a href="/shop" class="btn btn-primary btn-lg w-100">
                            Mulai Berbelanja
                        </a>
                    @else
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h5 class="card-title fw-bold mb-3">Langkah Verifikasi</h5>
                                
                                <ol class="ps-3">
                                    <li class="mb-2">
                                        Cek email Anda (termasuk folder Spam)
                                    </li>
                                    <li class="mb-2">
                                        Klik tautan verifikasi di email
                                    </li>
                                    <li>
                                        Akun Anda akan aktif secara otomatis
                                    </li>
                                </ol>
                            </div>
                        </div>

                        <!-- Resend Email Form -->
                        <div class="text-center mb-4">
                            <p class="text-muted mb-3">Email tidak sampai?</p>
                            
                            <form action="{{ route('verification.send') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-lg w-100">
                                    Kirim Ulang Email Verifikasi
                                </button>
                            </form>
                        </div>

                        <!-- Back Link -->
                        <p class="text-center mb-0">
                            <a href="{{ route('customer.logout') }}" class="text-decoration-none text-muted" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Keluar dari akun
                            </a>
                        </p>

                        <form id="logout-form" action="{{ route('customer.logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>

<style scoped>
.text-success {
    color: #0d36aa;
}

.card {
    border-radius: 12px;
}

.card-body {
    background-color: #f8f9fa;
}

.btn-outline-primary {
    color: #f17b0d;
    border-color: #f17b0d;
}

.btn-outline-primary:hover {
    background-color: #f17b0d;
    border-color: #f17b0d;
    color: white;
}

ol li {
    line-height: 1.8;
}
</style>
@endsection
