@extends('storefront.layouts.app-mobile')

@section('content')
<div class="orders-page">
    <!-- Header -->
    <div class="orders-header">
        <a href="{{ route('customer.dashboard') }}" class="orders-header__back">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5z"/>
            </svg>
        </a>
        <h1 class="orders-header__title">Riwayat Pesanan</h1>
    </div>

    <!-- Filters -->
    <div class="orders-filters">
        <form action="{{ route('customer.orders') }}" method="GET">
            <div class="orders-filters__search">
                <input type="text" name="search" class="search-input" placeholder="Cari nomor pesanan..." value="{{ $searchQuery ?? '' }}">
                <button type="submit" class="search-button">
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                    </svg>
                </button>
            </div>

            <select name="status" class="status-select" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="draft" {{ $selectedStatus === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="waiting_payment" {{ $selectedStatus === 'waiting_payment' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                <option value="dp_paid" {{ $selectedStatus === 'dp_paid' ? 'selected' : '' }}>DP Dibayar</option>
                <option value="paid" {{ $selectedStatus === 'paid' ? 'selected' : '' }}>Lunas</option>
                <option value="packed" {{ $selectedStatus === 'packed' ? 'selected' : '' }}>Dikemas</option>
                <option value="shipped" {{ $selectedStatus === 'shipped' ? 'selected' : '' }}>Dikirim</option>
                <option value="done" {{ $selectedStatus === 'done' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ $selectedStatus === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </form>
    </div>

    <!-- Orders List -->
    @if($orders->count() > 0)
    <div class="orders-list">
        @foreach($orders as $order)
        <a href="{{ route('customer.order.show', $order->id) }}" class="order-item">
            <div class="order-item__header">
                <div class="order-item__number">{{ $order->order_number }}</div>
                <div class="order-item__badges">
                    <span class="order-badge order-badge--{{
                        $order->status === 'draft' ? 'secondary' :
                        ($order->status === 'waiting_payment' ? 'warning' :
                        ($order->status === 'paid' ? 'success' :
                        ($order->status === 'cancelled' ? 'danger' : 'info')))
                    }}">
                        @if($order->status === 'draft')
                            Draft
                        @elseif($order->status === 'waiting_payment')
                            Menunggu Pembayaran
                        @elseif($order->status === 'dp_paid')
                            DP Dibayar
                        @elseif($order->status === 'paid')
                            Lunas
                        @elseif($order->status === 'packed')
                            Dikemas
                        @elseif($order->status === 'shipped')
                            Dikirim
                        @elseif($order->status === 'done')
                            Selesai
                        @elseif($order->status === 'cancelled')
                            Dibatalkan
                        @else
                            {{ ucfirst($order->status) }}
                        @endif
                    </span>
                </div>
            </div>

            <div class="order-item__info">
                <div class="order-item__row">
                    <span class="order-item__label">Tanggal</span>
                    <span class="order-item__value">{{ $order->created_at->format('d M Y, H:i') }}</span>
                </div>
                <div class="order-item__row">
                    <span class="order-item__label">Total</span>
                    <span class="order-item__value order-item__value--price">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="order-item__action">
                <span class="order-item__link">Lihat Detail</span>
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </div>
        </a>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="orders-pagination">
        {{ $orders->links('vendor.pagination.storefront') }}
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state__icon">
            <svg width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
                <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379l.626 2.5H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 13H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
            </svg>
        </div>
        <h2 class="empty-state__title">Tidak Ada Pesanan</h2>
        <p class="empty-state__text">Anda belum memiliki riwayat pesanan. Mulai belanja sekarang!</p>
        <a href="{{ route('shop.index') }}" class="btn btn--primary">Jelajahi Produk</a>
    </div>
    @endif
</div>
@endsection
