@extends('storefront.layouts.app-mobile')

@section('content')
<div class="dashboard">
    <!-- Welcome Header -->
    <div class="dashboard-welcome">
        <h1 class="dashboard-welcome__title">Halo, {{ $customer->name }}!</h1>
        <p class="dashboard-welcome__subtitle">Selamat datang di akun Anda</p>
    </div>

    <!-- Statistics Cards -->
    <div class="dashboard-stats">
        <div class="stat-card stat-card--primary">
            <div class="stat-card__icon">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379l.626 2.5H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 13H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
            </div>
            <div class="stat-card__content">
                <div class="stat-card__label">Total Pesanan</div>
                <div class="stat-card__value">{{ $totalOrders }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--success">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/>
                    <path d="M8.5 5.5V7h.5a.5.5 0 0 1 .5.5v5.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5V7.5a.5.5 0 0 1 .5-.5h.5V5.5a.5.5 0 0 1 1 0z"/>
                </svg>
            </div>
            <div class="stat-card__content">
                <div class="stat-card__label">Total Belanja</div>
                <div class="stat-card__value stat-card__value--small">Rp {{ number_format($totalSpent, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--warning">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                </svg>
            </div>
            <div class="stat-card__content">
                <div class="stat-card__label">Pesan Harus Dibayar</div>
                <div class="stat-card__value">{{ $duePaymentOrders }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--info">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zM8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0z"/>
                    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533l1.002-4.705z"/>
                    <circle cx="8" cy="4.5" r="1"/>
                </svg>
            </div>
            <div class="stat-card__content">
                <div class="stat-card__label">Pesanan dalam Proses</div>
                <div class="stat-card__value">{{ $inProcessOrders }}</div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Section -->
    <div class="dashboard-section">
        <div class="dashboard-section__header">
            <h2 class="dashboard-section__title">Pesanan Terbaru</h2>
            <a href="{{ route('customer.orders') }}" class="dashboard-section__link">Lihat Semua</a>
        </div>

        @if($recentOrders->count() > 0)
        @php
            $statusLabels = \App\Models\Order::getStatuses();
        @endphp
        <div class="order-list">
            @foreach($recentOrders as $order)
            <a href="{{ route('customer.order.show', $order->id) }}" class="order-card">
                <div class="order-card__header">
                    <div class="order-card__number">{{ $order->order_number }}</div>
                    @php
                        $badgeClass = match ($order->status) {
                            'waiting_dp', 'waiting_payment', 'dp_paid', 'product_ready', 'cancelled', 'cancelled_refund_pending' => 'warning',
                            'done' => 'success',
                            default => 'info',
                        };
                        $statusLabel = $statusLabels[$order->status] ?? ucfirst($order->status);
                    @endphp
                    <span class="order-badge order-badge--{{ $badgeClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>
                <div class="order-card__info">
                    <div class="order-card__date">{{ $order->created_at->format('d M Y') }}</div>
                    <div class="order-card__total">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="empty-state">
            <div class="empty-state__icon">
                <svg width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379l.626 2.5H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 13H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                </svg>
            </div>
            <p class="empty-state__text">Anda belum memiliki pesanan</p>
            <a href="{{ route('shop.index') }}" class="btn btn--primary btn--sm">Mulai Belanja</a>
        </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-actions">
        <a href="{{ route('customer.orders') }}" class="action-button">
            <div class="action-button__icon">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379l.626 2.5H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 13H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5z"/>
                </svg>
            </div>
            <span class="action-button__text">Riwayat</span>
        </a>
        
        <a href="{{ route('customer.profile') }}" class="action-button">
            <div class="action-button__icon">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm.027 7a6 6 0 0 0-11.054 0h11.054z"/>
                </svg>
            </div>
            <span class="action-button__text">Profil</span>
        </a>

        <a href="{{ route('shop.index') }}" class="action-button">
            <div class="action-button__icon">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379l.626 2.5H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 14H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5z"/>
                </svg>
            </div>
            <span class="action-button__text">Belanja</span>
        </a>

        <form action="{{ route('customer.logout') }}" method="POST" style="display: contents;">
            @csrf
            <button type="submit" class="action-button action-button--danger">
                <div class="action-button__icon">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
                        <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
                    </svg>
                </div>
                <span class="action-button__text">Logout</span>
            </button>
        </form>
    </div>
</div>
@endsection
