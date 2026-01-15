@extends('storefront.layouts.app-mobile')

@section('content')
<div class="notifications-page">
    <div class="notifications-header">
        <a href="{{ route('customer.dashboard') }}" class="notifications-header__back">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5z"/>
            </svg>
        </a>
        <h1 class="notifications-header__title">Notifikasi</h1>
    </div>

    @if($notifications->count() > 0)
        <div class="notifications-list">
            @foreach($notifications as $notification)
                @php
                    $data = $notification->data ?? [];
                    $actionUrl = $data['action_url'] ?? route('customer.order.show', $data['order_id'] ?? 0);
                @endphp
                <div class="notification-item {{ $notification->read_at ? '' : 'notification-item--unread' }}">
                    <a href="{{ $actionUrl }}" class="notification-item__content">
                        <div class="notification-item__title">{{ $data['title'] ?? 'Notifikasi' }}</div>
                        <div class="notification-item__message">{{ $data['message'] ?? '' }}</div>
                        <div class="notification-item__meta">
                            <span>{{ $notification->created_at->format('d M Y H:i') }}</span>
                            @if(!empty($data['order_number']))
                                <span>• {{ $data['order_number'] }}</span>
                            @endif
                        </div>
                    </a>
                    @if(!$notification->read_at)
                        <form method="POST" action="{{ route('customer.notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="notification-item__read">Tandai dibaca</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="notifications-pagination">
            {{ $notifications->links() }}
        </div>
    @else
        <div class="notifications-empty">
            <div class="notifications-empty__title">Belum ada notifikasi</div>
            <div class="notifications-empty__text">Notifikasi terkait pesanan Anda akan muncul di sini.</div>
        </div>
    @endif
</div>
@endsection
